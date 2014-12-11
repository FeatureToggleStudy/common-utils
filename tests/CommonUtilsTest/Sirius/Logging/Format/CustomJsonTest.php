<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\Format\CustomJson;

/**
 * Class ExtractorTest
 *
 * @package CommonUtilsTest\Sirius\Logging\Format
 */
class CustomJsonTest extends \PHPUnit_Framework_TestCase
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

        $jsonResult = sprintf('{"timestamp_msec":%s,"@fields":{"priority":7,"priorityName":"DEBUG","message":"Some logging test","status_code":200,"request_method":"POST"}}',$event['timestamp']->getTimestamp());

        $this->assertEquals($jsonResult, $result);
    }

    public function testSetGetDateFormat()
    {
        $format= 'c';
        $this->customjson->setDateTimeFormat($format);
        $this->assertEquals($this->customjson->getDateTimeFormat(), $format);
    }
}