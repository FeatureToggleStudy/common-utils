<?php
declare(strict_types=1);

namespace CommonUtils\Sirius\FeatureToggle;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

class ToggleServiceFactoryTest extends TestCase
{
    public function test_instance_can_be_created_with_feature_toggle_config_key()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', [
            'featureToggle' => [],
        ]);

        $factory = new ToggleServiceFactory();

        self::assertInstanceOf(ToggleService::class, $factory->createService($serviceManager));
    }

    public function test_instance_can_be_created_without_feature_toggle_config_key()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', []);

        $factory = new ToggleServiceFactory();

        self::assertInstanceOf(ToggleService::class, $factory->createService($serviceManager));
    }
}
