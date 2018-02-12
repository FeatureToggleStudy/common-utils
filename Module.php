<?php

namespace CommonUtils;

use CommonUtils\Sirius\Factory\OpgHttpClientFactory;
use CommonUtils\Sirius\Http\Client\SiriusHttpClient;
use CommonUtils\Sirius\Logging\ErrorHandling;
use CommonUtils\Sirius\Logging\Extractor;
use CommonUtils\Sirius\Logging\Factory\PsrLoggerAdapterFactory;
use CommonUtils\Sirius\Logging\PsrLoggerAdapter;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Log\Filter\Priority;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger as ZendLogger;

/**
 * Class Module.
 */
class Module
{
    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $serviceManager = $event->getApplication()->getServiceManager();

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        /** @var Extractor $extractor */
        $extractor = $serviceManager->get('CommonUtils\Extractor');
        $extractor->setRequest($event->getRequest());
        $extractor->setResponse($event->getResponse());

        $logger = $serviceManager->get('Logger');
        $logger->setExtractor($extractor);

        $this->enableSymfonyErrorHandler($serviceManager);

        //catches exceptions for errors during dispatch
        $sharedManager = $event->getApplication()->getEventManager()->getSharedManager();
        $sharedManager->attach(
            'Zend\Mvc\Application',
            'dispatch.error',
            function ($event) use ($serviceManager) {
                if ($event->getParam('exception')) {
                    $exception = $event->getParam('exception');
                    $serviceManager->get('Logger')->crit(
                        'Exception: [' . $exception->getMessage() . ']',
                        array(
                            'category' => 'Dispatch',
                            'stackTrace' => $exception->getTraceAsString(),
                        )
                    );
                }
            }
        );

        $this->enableFallbackExceptionHandler($event, $serviceManager, $extractor);

        //global catchall to log when a 400 or 500 error message is set on a response.
        //This is mainly for logging purposes.
        $eventManager->attach(
            MvcEvent::EVENT_FINISH,
            function ($e) use ($logger, $extractor) {
                if (!method_exists($e->getResponse(), 'getStatusCode')) {
                    return;
                }
                $statusCode = $e->getResponse()->getStatusCode();
                if ($statusCode >= 500) {
                    $e->getResponse()->setStatusCode($statusCode);
                    $extractor->setResponse($e->getResponse());
                    $logger->crit('Response: ' . $statusCode . '[' . $e->getResponse() . ']', ['category' => 'Event']);
                } elseif ($statusCode >= 400 && $statusCode < 500) {
                    $e->getResponse()->setStatusCode($statusCode);
                    $extractor->setResponse($e->getResponse());
                    $logger->warn('Response: ' . $statusCode . '[' . $e->getResponse() . ']', ['category' => 'Event']);
                }
            }
        );
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'CommonUtils\OpgHttpClient' => OpgHttpClientFactory::class,
                'CommonUtils\SiriusLogger' => function ($sm) {
                    $config = $sm->get('Config')['CommonUtils\Logger'];
                    $logger = new Sirius\Logging\Logger();

                    foreach ($config['writers'] as $writer) {
                        if ($writer['enabled']) {
                            $writerAdapter = new $writer['adapter']($writer['adapterOptions']['output']);
                            $logger->addWriter($writerAdapter);

                            if (!empty($writer['formatter'])) {
                                $writerFormatter = new $writer['formatter']($writer['formatterOptions']);
                                $writerAdapter->setFormatter($writerFormatter);
                            }
                            $writerAdapter->addFilter(
                                new Priority(
                                    $writer['filter']
                                )
                            );
                        }
                    }

                    if (!isset($config['CommonUtils\Logger']['symfonyErrorHandler'])
                        || true !== $config['CommonUtils\Logger']['symfonyErrorHandler']) {
                        ZendLogger::registerErrorHandler($logger);
                    }

                    return $logger;
                },
                'CommonUtils\SiriusFrontEndLogger' => function ($sm) {
                    return new Sirius\Logging\FrontEndLogger($sm->get('CommonUtils\SiriusLogger'));
                },
                'CommonUtils\Extractor' => function ($sm) {
                    $config = $sm->get('Config')['CommonUtils\Logger']['extractions'];
                    $extractor = new Sirius\Logging\Extractor($config);

                    return $extractor;
                },
                SiriusHttpClient::class => OpgHttpClientFactory::class,
                'PsrLogger' => PsrLoggerAdapterFactory::class,
            ),
        );
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    private function enableSymfonyErrorHandler(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $commonUtilsConfig = isset($config['CommonUtils\Logger']) ? $config['CommonUtils\Logger'] : [];

        if (!isset($commonUtilsConfig['symfonyErrorHandler']) || true !== $commonUtilsConfig['symfonyErrorHandler']) {
            return;
        }

        /** @var PsrLoggerAdapter $psrLogger */
        $psrLogger = $serviceLocator->get('PsrLogger');
        ErrorHandling::enable($psrLogger, $commonUtilsConfig);
    }

    /**
     * Catches exceptions in application code that are uncaught. For example if a database server goes down.
     *
     * @param MvcEvent $event
     * @param ServiceLocatorInterface $serviceLocator
     * @param Extractor $extractor
     */
    private function enableFallbackExceptionHandler(
        MvcEvent $event,
        ServiceLocatorInterface $serviceLocator,
        Extractor $extractor
    ) {
        $config = $serviceLocator->get('Config');

        if (isset($config['CommonUtils\Logger']['symfonyErrorHandler'])
            && true === $config['CommonUtils\Logger']['symfonyErrorHandler']) {
            return;
        }

        set_exception_handler(
            function ($exception) use ($event, $serviceLocator, $extractor) {
                //ensure that the application log, logs a 500 error
                if ($event->getResponse() instanceof Response) {
                    $event->getResponse()->setStatusCode(500);
                }
                $extractor->setResponse($event->getResponse());
                http_response_code(500);
                $serviceLocator->get('Logger')->crit(
                    'Exception: [' . $exception->getMessage() . ']',
                    [
                        'category' => 'API',
                        'stackTrace' => $exception->getTraceAsString(),
                    ]
                );
            }
        );
    }
}
