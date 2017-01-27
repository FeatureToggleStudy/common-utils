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

        // Generate new request
        $restRequest = $this->generateRequest($serviceLocator);

        // Set up client with new adapter and request to backend
        $client = new SiriusHttpClient();
        $client->setLogger($logger)
            ->resetParameters()
            ->setAdapter($config['opg-backend']['adapter'])
            ->setRequest($restRequest);

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
        if (!empty($config['opg-backend']['username']) && !empty($config['opg-backend']['password'])) {
            $client->setAuth($config['opg-backend']['username'], $config['opg-backend']['password']);
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
        $httpHeaders = $httpRequest->getHeaders();

        $config      = $serviceLocator->get('Config');

        $restRequest = new ZendHttpRequest();
        $restRequest->setUri($config['opg-backend']['uri']);
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
}
