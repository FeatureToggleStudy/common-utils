<?php

namespace CommonUtils\Sirius\Factory;

use CommonUtils\Sirius\Http\Client\SiriusHttpClient;
use CommonUtils\Sirius\Logging\Logger;
use Zend\Http\Request as ZendHttpRequest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OpgHttpClientFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SiriusHttpClient
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Logger $logger */

        $logger = $serviceLocator->get('CommonUtils\SiriusLogger');
        $config = $serviceLocator->get('Config');

        $request = $this->generateRequest($serviceLocator);

        // Set up client with new adapter and request to backend
        $client = new SiriusHttpClient($config['sirius_http_client']['uri'], $config['sirius_http_client']['options']);
        $client->setLogger($logger)
            ->resetParameters()
            ->setAdapter($config['sirius_http_client']['adapter']);

        if (!empty($request)) {
            $client->setRequest($request);
        }

        if (array_key_exists(CURLOPT_SSL_VERIFYPEER, $config['curlopts'])) {
            $client->getAdapter()->setCurlOption(CURLOPT_SSL_VERIFYPEER, $config['curlopts'][CURLOPT_SSL_VERIFYPEER]);
        }

        if (array_key_exists(CURLOPT_SSL_VERIFYHOST, $config['curlopts'])) {
            $client->getAdapter()->setCurlOption(CURLOPT_SSL_VERIFYHOST, $config['curlopts'][CURLOPT_SSL_VERIFYHOST]);
        }

        if (array_key_exists(CURLOPT_CAINFO, $config['curlopts'])) {
            $client->getAdapter()->setCurlOption(CURLOPT_CAINFO, $config['curlopts'][CURLOPT_CAINFO]);
        }

        $client = $this->setupClientAuth($client, $config);

        return $client;
    }

    /**
     * Sets Authentication on the client
     *
     * @param SiriusHttpClient $client
     * @param $config
     * @return SiriusHttpClient
     */
    private function setupClientAuth(SiriusHttpClient $client, $config)
    {
        if (!empty($config['sirius_http_client']['username']) && !empty($config['sirius_http_client']['password'])) {
            $client->setAuth($config['sirius_http_client']['username'], $config['sirius_http_client']['password']);
        }

        return $client;
    }

    /**
     * Generates an HTTP request object to backend Sirius with appropriate headers
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ZendHttpRequest
     */
    private function generateRequest(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ZendHttpRequest $httpRequest */
        $httpRequest = $serviceLocator->get('Request');

        if ($httpRequest instanceof ZendHttpRequest) {
            $httpHeaders = $httpRequest->getHeaders();

            $config = $serviceLocator->get('Config');

            $restRequest = new ZendHttpRequest();
            $restRequest->setUri($config['sirius_http_client']['uri']);
            $restHeaders = $restRequest->getHeaders();

            if ($httpHeaders->get('X-REQUEST-ID')) {
                $restHeaders->addHeaderLine(
                    'X-REQUEST-ID',
                    $httpHeaders->get('X-REQUEST-ID')->getFieldValue()
                );
            }

            if ($serviceLocator->get('AuthenticationService')->hasIdentity()) {
                $restHeaders->addHeaderLine(
                    'http-secure-token',
                    $serviceLocator->get('AuthenticationService')->getIdentity()->getToken()
                );
            }
            $restRequest->setHeaders($restHeaders);

            return $restRequest;
        }

        // Zend\Console\Requests
        return new ZendHttpRequest();
    }
}
