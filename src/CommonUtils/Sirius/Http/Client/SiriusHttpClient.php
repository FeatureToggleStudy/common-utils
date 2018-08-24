<?php

namespace CommonUtils\Sirius\Http\Client;

use CommonUtils\Sirius\Logging\Logger;
use Traversable;
use Zend\Http\Client as ZendHttpClient;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Request;
use Blackfire\Client as BlackfireClient;

class SiriusHttpClient extends ZendHttpClient
{
    const REQUEST_LOG_MESSAGE = 'Making HTTP request: %s %s';
    const RESPONSE_LOG_MESSAGE = '%s Response received: %s %s (%d bytes)';

    /** @var Logger $logger */
    private $logger;

    /**
     * Constructor
     *
     * @param string $uri
     * @param array|Traversable $options
     * @param null $logger
     */
    public function __construct($uri = null, $options = null, $logger = null)
    {
        parent::__construct($uri, $options);
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function send(Request $request = null)
    {

        // This is to work around the fact that Zend populates an internal $request object, and you can also send through $request here.
        // We always want $request to be a real object, so this takes care of that.
        if($request === null) {
            $request = $this->getRequest();
        }

        $preRequest = microtime(true);

        $this->logger->info(
            sprintf(self::REQUEST_LOG_MESSAGE, $this->getMethod(), $this->getUri()->toString()),
            ['category' => 'HTTP_CLIENT']
        );

        // The actual HTTP-Header sent to the frontend is: "X-Sirius-Blackfiretrigger" but nginx config converts it to the HTTP_X format before sending to php-fpm
        if (isset($_SERVER['HTTP_X_SIRIUS_BLACKFIRETRIGGER']) && $_SERVER['HTTP_X_SIRIUS_BLACKFIRETRIGGER'] === 'true' && extension_loaded('blackfire')) {
            $existingHeaders = $request->getHeaders();
            $existingHeaders->addHeader(new GenericHeader('X-Sirius-Blackfiretrigger', 'true'));
            $request->setHeaders($existingHeaders);
        }

        // Make the HTTP sub-request
        $response = parent::send($request);

        $contentType = 'Content-Type: application/octet-stream';
        if ($response->getHeaders()->has('Content-Type')) {
            $contentType = $response->getHeaders()->get('Content-Type')->toString();
        }

        if ((int) $response->getStatusCode() >= 500) {
            $this->logger->warn(
                sprintf(
                    self::RESPONSE_LOG_MESSAGE,
                    $contentType,
                    $response->getStatusCode(),
                    $response->getReasonPhrase(),
                    strlen($response->getContent())
                ),
                [
                    'category' => 'HTTP_CLIENT',
                    'sub_request_time' => microtime(true) - $preRequest,
                    'response' => $response->getBody()
                ]
            );
        } else {
            $this->logger->info(
                sprintf(
                    self::RESPONSE_LOG_MESSAGE,
                    $contentType,
                    $response->getStatusCode(),
                    $response->getReasonPhrase(),
                    strlen($response->getContent())
                ),
                ['category' => 'HTTP_CLIENT', 'sub_request_time' => microtime(true) - $preRequest]
            );
        }

        return $response;
    }
}
