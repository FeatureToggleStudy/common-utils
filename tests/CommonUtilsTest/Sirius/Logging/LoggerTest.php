<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\Logger;

/**
 * Class ExtractorTest
 *
 * @package CommonUtilsTest\Sirius\Logging
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $sampleLoggerConfigFile = include(__DIR__.'/../../../../config/sample.logger.global.php');
        $sampleConfig = $sampleLoggerConfigFile['CommonUtils\Logger'];
        $this->logger = new Logger();
        foreach ($sampleConfig['writers'] as $writer) {
            if ($writer['enabled']) {
                $writerAdapter = new $writer['adapter']($writer['adapterOptions']['output']);
                $this->logger->addWriter($writerAdapter);
            }
        }
    }

    public function testLogMethod()
    {
        $extractor = \Mockery::mock('CommonUtils\Sirius\Logging\Extractor');
        $extractor->shouldReceive('process')
                  ->andReturn(array('status_code' => 200));
        $this->logger->setExtractor($extractor);
        $this->logger->log(6,'This is a log message');


    }


}