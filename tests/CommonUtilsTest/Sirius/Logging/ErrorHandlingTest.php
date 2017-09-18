<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\ErrorHandling;
use ErrorException;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class ErrorHandlingTest extends PHPUnit_Framework_TestCase
{
    public function test_enable_with_empty_config()
    {
        ErrorHandling::enable(new TestLogger(), []);
    }

    public function test_deprecation_warnings_are_logged_with_dev_settings()
    {
        $logger = new TestLogger();
        $config = [
            'displayExceptions' => true,
            'logLocalVariables' => true,
        ];

        ErrorHandling::enable($logger, $config);
        trigger_error('Deprecated', E_USER_DEPRECATED);

        self::assertSame(1, $logger->getMessages()->count());
        $record = $logger->getMessages()->last();
        self::assertSame(LogLevel::INFO, $record['level']);
        self::assertInstanceOf(ErrorException::class, $record['context']['exception']);
    }

    public function test_non_fatal_errors_are_converted_to_an_exception_with_dev_settings()
    {
        $config = [
            'displayExceptions' => true,
            'logLocalVariables' => true,
        ];

        ErrorHandling::enable(new TestLogger(), $config);
        try {
            trigger_error('Notice', E_USER_NOTICE);
            $this->fail('ErrorException expected');
        } catch (ErrorException $e) {
            // if an exception is thrown, the test passed
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function test_fatal_errors_are_converted_to_an_exception_with_prod_settings()
    {
        $config = [
            'displayExceptions' => false,
            'logLocalVariables' => false,
        ];

        ErrorHandling::enable(new TestLogger(), $config);
        try {
            trigger_error('Error', E_USER_ERROR);
            $this->fail('ErrorException expected');
        } catch (ErrorException $e) {
            // if an exception is thrown, the test passed
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function test_non_fatal_errors_are_logged_with_prod_settings()
    {
        $logger = new TestLogger();
        $config = [
            'displayExceptions' => false,
            'logLocalVariables' => false,
        ];

        ErrorHandling::enable($logger, $config);
        trigger_error('Warning', E_USER_WARNING);

        self::assertSame(1, $logger->getMessages()->count());
        $record = $logger->getMessages()->last();
        self::assertSame(LogLevel::WARNING, $record['level']);
        self::assertInstanceOf(ErrorException::class, $record['context']['exception']);
    }
}
