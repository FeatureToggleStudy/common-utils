<?php

namespace CommonUtils\Sirius\Logging;

class FrontEndLogger
{
    const FRONT_LOG_LEVEL_OFF = 0;
    const FRONT_LOG_LEVEL_ERROR = 20;
    const FRONT_LOG_LEVEL_WARN = 30;
    const FRONT_LOG_LEVEL_INFO = 40;
    const FRONT_LOG_LEVEL_DEBUG = 50;
    const FRONT_LOG_LEVEL_LOG = 60;
    const FRONT_LOG_LEVEL_ALL = 100;

    /** @var Logger $logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $jsonString
     */
    public function log($jsonString)
    {
        $json = json_decode($jsonString, true);

        switch ($json['priority']) {
            case self::FRONT_LOG_LEVEL_ALL:
            case self::FRONT_LOG_LEVEL_LOG:
            case self::FRONT_LOG_LEVEL_DEBUG:
                $this->logger->debug($json['message'], $json);
                break;
            default:
            case self::FRONT_LOG_LEVEL_INFO:
                $this->logger->info($json['message'], $json);
                break;
            case self::FRONT_LOG_LEVEL_WARN:
                $this->logger->warn($json['message'], $json);
                break;
            case self::FRONT_LOG_LEVEL_ERROR:
                if ($json['type'] != 'exception') {
                    $this->logger->err($json['message'], $json);
                } else {
                    $this->logger->crit($json['message'], $json);
                }
                break;
            case self::FRONT_LOG_LEVEL_OFF:
                break;
        }
    }
}
