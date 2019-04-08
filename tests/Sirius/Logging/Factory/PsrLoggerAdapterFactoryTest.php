<?php

namespace CommonUtilsTest\Sirius\Logging\Factory;

use CommonUtils\Sirius\Logging\Factory\PsrLoggerAdapterFactory;
use CommonUtils\Sirius\Logging\Logger;
use CommonUtils\Sirius\Logging\PsrLoggerAdapter;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class PsrLoggerAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager;
        $serviceManager->setService('Logger', $this->getMock(Logger::class));

        $factory = new PsrLoggerAdapterFactory();
        $this->assertInstanceOf(PsrLoggerAdapter::class, $factory->createService($serviceManager));
    }
}
