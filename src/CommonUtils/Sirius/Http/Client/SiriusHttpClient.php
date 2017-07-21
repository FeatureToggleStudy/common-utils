<?php

namespace CommonUtils\Sirius\Http\Client;

use CommonUtils\Sirius\Logging\Logger;
use Traversable;
use Zend\Http\Client as ZendHttpClient;
use Zend\Http\Request;

class SiriusHttpClient extends ZendHttpClient
{
    const REQUEST_LOG_MESSAGE = 'Making HTTP request: %s %s';
    const RESPONSE_LOG_MESSAGE = '%s Response received: %s %s (%s bytes)';
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
        $preRequest = microtime(true);

        $this->logger->info(
            sprintf(self::REQUEST_LOG_MESSAGE, $this->getMethod(), $this->getUri()->toString()),
            ['category' => 'HTTP_CLIENT']
        );

        $response = parent::send($request);

        $contentType = 'content-type/unknown';
        if ($response->getHeaders()->has('Content-Type')) {
            $contentType = $response->getHeaders()->get('Content-Type');
        }

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

        return $response;
    }
}
