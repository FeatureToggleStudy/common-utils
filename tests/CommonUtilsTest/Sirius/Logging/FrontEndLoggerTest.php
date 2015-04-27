<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\FrontEndLogger;
use CommonUtils\Sirius\Logging\Logger;

class FrontEndLoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $mockLogger;

    public function setup()
    {
        $this->mockLogger = $this->getMockBuilder('CommonUtils\Sirius\Logging\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('debug', 'info', 'warn', 'err', 'crit', '__destruct'))
            ->getMock();
    }

    /**
     * @dataProvider logMessageProvider
     * @param string|null $method - the name of expected method to be called
     * @param array $json - the decoded json message from the front end
     */
    public function testLog($method, $json)
    {
        if ($method) {
            $this->mockLogger->expects($this->once())
                ->method($method)
                ->with($json['message']['content']['message'], $json);
        } else {
            $this->mockLogger->expects($this->never())
                ->method('log');
        }

        $logger = new FrontEndLogger($this->mockLogger);
        $logger->log(json_encode($json));
    }

    public function logMessageProvider()
    {
        return array(
            array(
                'debug',
                array(
                    'type' => 'error',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_ALL,
                    'message' => array(
                        'content' => array(
                            'message' => 'test all message'
                        ),
                    ),
                ),
            ),
            array(
                'debug',
                array(
                    'type' => 'error',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_LOG,
                    'message' => array(
                        'content' => array(
                            'message' => 'test log message'
                        ),
                    ),
                ),
            ),
            array(
                'debug',
                array(
                    'type' => 'error',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_DEBUG,
                    'message' => array(
                        'content' => array(
                            'message' => 'test debug message'
                        ),
                    ),
                ),
            ),
            array(
                'info',
                array(
                    'type' => 'error',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_INFO,
                    'message' => array(
                        'content' => array(
                            'message' => 'test info message'
                        ),
                    ),
                ),
            ),
            array(
                'warn',
                array(
                    'type' => 'warn',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_WARN,
                    'message' => array(
                        'content' => array(
                            'message' => 'test warn message'
                        ),
                    ),
                ),
            ),
            array(
                'err',
                array(
                    'type' => 'error',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_ERROR,
                    'message' => array(
                        'content' => array(
                            'message' => 'test error message'
                        ),
                    ),
                ),
            ),
            array(
                'crit',
                array(
                    'type' => 'exception',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_ERROR,
                    'message' => array(
                        'content' => array(
                            'message' => 'test error exception message'
                        ),
                    ),
                ),
            ),
            array(
                null,
                array(
                    'type' => 'exception',
                    'priority' => FrontEndLogger::FRONT_LOG_LEVEL_OFF,
                    'message' => array(
                        'content' => array(
                            'message' => 'test log level off message'
                        ),
                    ),
                ),
            ),
        );
    }
}
