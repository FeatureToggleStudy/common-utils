<?php
declare(strict_types=1);

namespace CommonUtils\Sirius\Logging;

use Psr\Log\AbstractLogger as PsrAbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Zend\Log\Logger as ZendLogger;

/**
 * PSR-3 logger adapter for Zend\Log\Logger
 *
 * Decorates a Zend\Log\Logger to allow it to be used anywhere a PSR-3 logger is expected.
 *
 * Adapted from https://github.com/zendframework/zend-log/blob/release-2.9.2/test/PsrLoggerAdapterTest.php
 * Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */
class PsrLoggerAdapter extends PsrAbstractLogger
{
    /**
     * @var ZendLogger
     */
    protected $logger;

    /**
     * Map PSR-3 LogLevels to priority
     *
     * @var array
     */
    protected $psrPriorityMap = [
        LogLevel::EMERGENCY => ZendLogger::EMERG,
        LogLevel::ALERT     => ZendLogger::ALERT,
        LogLevel::CRITICAL  => ZendLogger::CRIT,
        LogLevel::ERROR     => ZendLogger::ERR,
        LogLevel::WARNING   => ZendLogger::WARN,
        LogLevel::NOTICE    => ZendLogger::NOTICE,
        LogLevel::INFO      => ZendLogger::INFO,
        LogLevel::DEBUG     => ZendLogger::DEBUG,
    ];

    /**
     * Constructor
     *
     * @param ZendLogger $logger
     */
    public function __construct(ZendLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns composed ZendLogger instance.
     *
     * @return ZendLogger
     */
    public function getLogger(): ZendLogger
    {
        return $this->logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     * @throws InvalidArgumentException if log level is not recognized
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($this->psrPriorityMap[$level])) {
            throw new InvalidArgumentException(sprintf(
                '$level must be one of PSR-3 log levels; received %s',
                var_export($level, true)
            ));
        }

        $priority = $this->psrPriorityMap[$level];
        $this->logger->log($priority, $message, $context);
    }
}
