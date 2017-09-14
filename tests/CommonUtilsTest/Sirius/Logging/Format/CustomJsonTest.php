<?php

namespace CommonUtilsTest\Sirius\Logging\Format;

use CommonUtils\Sirius\Logging\Format\CustomJson;
use PHPUnit_Framework_TestCase;

/**
 * Class ExtractorTest.
 */
class CustomJsonTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->customjson = new CustomJson();
    }

    public function testFormatReturnsJson()
    {
        $event['timestamp'] = new \DateTime();
        $event['priority'] = 7;
        $event['priorityName'] = 'DEBUG';
        $event['message'] = 'Some logging test';
        $event['extra'] = array('status_code' => 200, 'request_method' => 'POST');
        $result = $this->customjson->format($event);

        $jsonResult = sprintf('{"timestamp":%s,"priority":7,"priorityName":"DEBUG","message":"Some logging test","status_code":200,"request_method":"POST"}',
            $event['timestamp']->getTimestamp());

        $this->assertEquals($jsonResult, $result);
    }
}
