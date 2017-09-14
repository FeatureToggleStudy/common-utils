<?php

namespace CommonUtils\Sirius\Logging;

use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

final class ErrorHandling
{
    /**
     * @var bool
     */
    private static $enabled = false;

    /**
     * @param LoggerInterface $psrLogger
     * @param array $config
     */
    public static function enable(LoggerInterface $psrLogger, array $config)
    {
        $displayExceptions = isset($config['displayExceptions']) ? (bool) $config['displayExceptions'] : false;

        if (true === $displayExceptions && 'cli' !== PHP_SAPI) {
            ini_set('display_errors', 0);
            ExceptionHandler::register();
        }

        $errorReportingLevel = E_ALL;
        $errorHandler = ErrorHandler::register();

        // Sets the PHP error levels for which local variables are preserved.
        // NOTE: Should be disabled for environments containing real user data (Pre-Prod, Prod).
        $logLocalVariables = isset($config['logLocalVariables']) ? (bool) $config['logLocalVariables'] : false;
        $errorHandler->scopeAt($logLocalVariables ? $errorReportingLevel : 0, true);

        // Sets the PHP error levels that throw an exception when a PHP error occurs.
        // For local dev environments it should trigger an exception at all levels (E_ALL).
        $errorHandler->throwAt($displayExceptions ? $errorReportingLevel : 0, true);

        $errorHandler->setDefaultLogger($psrLogger, $errorReportingLevel, false);

        DebugClassLoader::enable();
    }
}
