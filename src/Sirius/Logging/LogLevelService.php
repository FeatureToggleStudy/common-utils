<?php

namespace CommonUtils\Sirius\Logging;

use OutOfRangeException;
use Zend\Log\Logger;

class LogLevelService
{
    const ACCEPTABLE_LOG_LEVELS = [
        Logger::EMERG,
        Logger::ALERT,
        Logger::CRIT,
        Logger::ERR,
        Logger::WARN,
        Logger::NOTICE,
        Logger::INFO,
        Logger::DEBUG
    ];
    const DEFAULT_ENV_VAR_STACKNAME = 'OPG_STACKNAME';
    const ERR_MSG_OUT_OF_RANGE = '"%s" is not a valid Zend log level';
    const PROD_MAX_LOG_LEVEL = Logger::INFO;

    /**
     * @param int $logLevel
     * @throws OutOfRangeException
     */
    private static function assertLogLevelIsInRange(int $logLevel): void
    {
        if (!in_array($logLevel, self::ACCEPTABLE_LOG_LEVELS)) {
            throw new OutOfRangeException(sprintf(self::ERR_MSG_OUT_OF_RANGE, $logLevel));
        }
    }

    /**
     * @param string $targetEnvironment
     * @return bool
     */
    private static function isTargetProduction(string $targetEnvironment): bool
    {
        return strpos(getenv($targetEnvironment), 'prod') !== false;
    }

    /**
     * @param string $logLevelEnvVarName Name of the environment variable, holding the log level
     * @param string $stackNameEnvVarName Name of the environment variable, holding the environment/stack name
     * @param int $fallbackLogLevel The fall back log level when the environment variable is not set
     * @return int
     */
    public static function getLogLevel(
        string $logLevelEnvVarName,
        string $stackNameEnvVarName = self::DEFAULT_ENV_VAR_STACKNAME,
        int $fallbackLogLevel = self::PROD_MAX_LOG_LEVEL
    ): int
    {

        $logLevel = getenv($logLevelEnvVarName) ? intval(getenv($logLevelEnvVarName)) : $fallbackLogLevel;

        self::assertLogLevelIsInRange($logLevel);

        if (self::isTargetProduction($stackNameEnvVarName) && $logLevel > Logger::INFO) {
            $logLevel = self::PROD_MAX_LOG_LEVEL;
        }

        return $logLevel;
    }
}
