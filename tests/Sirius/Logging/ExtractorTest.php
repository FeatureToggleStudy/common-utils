<?php

namespace CommonUtilsTest\Sirius\Logging;

use CommonUtils\Sirius\Logging\Extractor;

/**
 * Class ExtractorTest
 *
 * @package CommonUtilsTest\Sirius\Logging
 */
class ExtractorTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->config = array(0 => array('property' => 'response',
                                         'method_name' => 'getStatusCode',
                                         'method_values' => 'status_code'),
                              1 => array('property' => 'request',
                                         'method_name' => 'getServer',
                                         'method_values' => array(
                                         'SERVER_NAME','REQUEST_METHOD')));

        $this->request = \Mockery::mock('Zend\Http\PhpEnvironment\Request');
        $this->request->shouldReceive('getServer')
                      ->with('SERVER_NAME')
                      ->andReturn('testing.com');

        $this->request->shouldReceive('getServer')
            ->with('REQUEST_METHOD')
            ->andReturn('POST');

        $this->response = \Mockery::mock('Zend\Http\PhpEnvironment\Response');
        $this->response->shouldReceive('getStatusCode')
                       ->andReturn(200);

        $this->expectedOutput = array('status_code' => 200,
                                      'server_name' => 'testing.com',
                                      'request_method' => 'POST'
                            );
    }

    public function testExtractorExtractsCorrectValues()
    {
        $this->extractor = new Extractor($this->config);
        $this->extractor->setRequest($this->request);
        $this->extractor->setResponse($this->response);

        $result = $this->extractor->process();
        $this->assertEquals($result, $this->expectedOutput);
    }

    public function testGettersAndSetters()
    {
        $this->extractor = new Extractor($this->config);
        $this->extractor->setRequest($this->request);
        $this->extractor->setResponse($this->response);

        $this->assertEquals($this->extractor->getRequest(), $this->request);
        $this->assertEquals($this->extractor->getResponse(), $this->response);
    }


}