<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\PsrPlaceholder;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * @coversDefaultClass CommonUtils\Sirius\Logging\PsrPlaceholder
 *
 * Adapted from https://github.com/zendframework/zend-log/blob/release-2.9.2/test/Processor/PsrPlaceholderTest.php
 * Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */
class PsrPlaceholderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider pairsProvider
     * @covers ::process
     */
    public function testReplacement($val, $expected)
    {
        $psrProcessor = new PsrPlaceholder;
        $event = $psrProcessor->process([
            'message' => '{foo}',
            'extra'   => ['foo' => $val]
        ]);
        $this->assertEquals($expected, $event['message']);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function pairsProvider()
    {
        return [
            'string'     => ['foo', 'foo'],
            'string-int' => ['3', '3'],
            'int'        => [3, '3'],
            'null'       => [null, ''],
            'true'       => [true, '1'],
            'false'      => [false, ''],
            'stdclass'   => [new stdClass, '[object stdClass]'],
            'array'      => [[], '[array]'],
        ];
    }
}
