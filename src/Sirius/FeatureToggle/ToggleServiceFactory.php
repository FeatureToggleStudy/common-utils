<?php
declare(strict_types=1);

namespace CommonUtils\Sirius\FeatureToggle;

use CommonUtils\Sirius\Migration\Zf3Container;
use CommonUtils\Sirius\Migration\Zf3FactoryInterface;
use Interop\Container\ContainerInterface;
use Qandidate\Toggle\ToggleCollection\InMemoryCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Qandidate\Toggle\Serializer\InMemoryCollectionSerializer;
use Qandidate\Toggle\ToggleManager;

class ToggleServiceFactory implements FactoryInterface, Zf3FactoryInterface
{
    /**
     * Create ToggleService.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ToggleService
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ToggleService
    {
        return $this(new Zf3Container($serviceLocator), ToggleService::class);
    }

    /**
     * Create ToggleService.
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return ToggleService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ToggleService
    {
        /** @var array $config */
        $config = $container->get('Config');
        if (isset($config['featureToggle'])) {
            $collectionSerializer = new InMemoryCollectionSerializer();
            $collection = $collectionSerializer->deserialize($config['featureToggle']);
        } else {
            $collection = new InMemoryCollection();
        }

        return new ToggleService(
            new ToggleManager($collection)
        );
    }
}
