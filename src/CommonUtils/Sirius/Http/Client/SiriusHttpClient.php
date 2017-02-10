<?php

namespace CommonUtils\Sirius\Http\Client;

use CommonUtils\Sirius\Logging\Logger;
use Traversable;
use Zend\Http\Client as ZendHttpClient;

class SiriusHttpClient extends ZendHttpClient
{
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
}
