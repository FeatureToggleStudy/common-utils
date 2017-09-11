<?php

namespace CommonUtils\Sirius\Logging\Factory;

use CommonUtils\Sirius\Logging\Logger;
use CommonUtils\Sirius\Logging\PsrLoggerAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PsrLoggerAdapterFactory implements FactoryInterface
{
    /**
     * Create PsrLoggerAdapter.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PsrLoggerAdapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Logger $logger */
        $logger = $serviceLocator->get('Logger');

        return new PsrLoggerAdapter($logger);
    }
}
