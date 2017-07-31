<?php

namespace CommonUtils\Sirius\Controller\Factory;

use CommonUtils\Sirius\CacheWarmer\DoctrineCacheWarmer;
use CommonUtils\Sirius\Controller\CacheWarmerController;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheWarmerControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     *
     * @return CacheWarmerController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();

        /** @var Console $console */
        $console = $mainServiceManager->get('Console');

        /** @var ModuleManager $moduleManager */
        $moduleManager = $mainServiceManager->get('ModuleManager');
        $modules = array_flip($moduleManager->getModules());

        $cacheWarmers = [];
        if (isset($modules['DoctrineORMModule'])) {
            $cacheWarmers[] = new DoctrineCacheWarmer();
        }

        return new CacheWarmerController($console, $cacheWarmers);
    }
}
