<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\LogLevelService;
use OutOfRangeException;
use PHPUnit_Framework_TestCase;

//use PHPUnit\Framework\TestCase;

class LogLevelServiceTest extends PHPUnit_Framework_TestCase
{
    const DUMMY_ENV_VAR_STACKNAME = 'OPG_STACKNAME';
    const DUMMY_ENV_VAR_LOGLEVEL = 'OPG_LOGGER_APPLICATION_LOG_LEVEL';

    /**
     * @param string $stackName
     */
    private function setStackEnvVar(string $stackName)
    {
        putenv(self::DUMMY_ENV_VAR_STACKNAME . '=' . $stackName);
    }

    /**
     * @param int $logLevel
     */
    private function setLogEnvVar(int $logLevel)
    {
        putenv(self::DUMMY_ENV_VAR_LOGLEVEL . '=' . $logLevel);
    }

    public function setUp()
    {
        putenv(self::DUMMY_ENV_VAR_STACKNAME . '=');
        putenv(self::DUMMY_ENV_VAR_LOGLEVEL . '=');
    }

    public function test_service_only_accepts_valid_zend_log_levels()
    {
        foreach (LogLevelService::ACCEPTABLE_LOG_LEVELS as $logLevel) {
            $this->setLogEnvVar($logLevel);
            $this->assertEquals($logLevel,
                LogLevelService::getLogLevel(
                    self::DUMMY_ENV_VAR_LOGLEVEL,
                    self::DUMMY_ENV_VAR_STACKNAME,
                    $logLevel
                )
            );
        }

        $unexpectedLogLevel = 8;
        $this->setLogEnvVar($unexpectedLogLevel);
        $this->setExpectedException(
            OutOfRangeException::class,
            sprintf(LogLevelService::ERR_MSG_OUT_OF_RANGE, $unexpectedLogLevel)
        );

        LogLevelService::getLogLevel(
            self::DUMMY_ENV_VAR_LOGLEVEL,
            self::DUMMY_ENV_VAR_STACKNAME
        );
    }

    public function test_service_bumps_down_log_level_on_production_environments()
    {
        $productionEnvironments = [
            'preprod',
            'preprod-analytical',
            'preprod2',
            'preprod3',
            'preproddata',
            'production',
        ];

        foreach ($productionEnvironments as $environment) {
            $this->setStackEnvVar($environment);
            $this->assertEquals(
                LogLevelService::PROD_MAX_LOG_LEVEL,
                LogLevelService::getLogLevel(
                    self::DUMMY_ENV_VAR_LOGLEVEL,
                    self::DUMMY_ENV_VAR_STACKNAME,
                    LogLevelService::PROD_MAX_LOG_LEVEL + 1
                )
            );
        }
    }

    public function test_service_not_overwriting_log_level_on_non_production_environments()
    {
        $developmentEnvironments = [
            'feature1',
            'feature2',
            'feature3',
            'feature4',
            'feature5',
            'feature6',
            'demo',
            'integration',
        ];

        foreach ($developmentEnvironments as $environment) {
            $this->setStackEnvVar($environment);
            $this->assertEquals(
                LogLevelService::PROD_MAX_LOG_LEVEL,
                LogLevelService::getLogLevel(
                    self::DUMMY_ENV_VAR_LOGLEVEL,
                    self::DUMMY_ENV_VAR_STACKNAME,
                    LogLevelService::PROD_MAX_LOG_LEVEL
                )
            );
        }
    }
}
