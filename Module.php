<?php

namespace CommonUtils;

use CommonUtils\Sirius\Factory\OpgHttpClientFactory;
use CommonUtils\Sirius\Http\Client\SiriusHttpClient;
use CommonUtils\Sirius\Logging\ErrorHandling;
use CommonUtils\Sirius\Logging\Factory\PsrLoggerAdapterFactory;
use CommonUtils\Sirius\Logging\PsrLoggerAdapter;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Log\Filter\Priority;

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

        $extractor = $serviceManager->get('CommonUtils\Extractor');
        $extractor->setRequest($event->getRequest());
        $extractor->setResponse($event->getResponse());

        $logger = $serviceManager->get('Logger');
        $logger->setExtractor($extractor);

        /** @var PsrLoggerAdapter $psrLogger */
        $psrLogger = $serviceManager->get('PsrLogger');
        $config = $serviceManager->get('Config');
        ErrorHandling::enable($psrLogger, isset($config['CommonUtils\Logger']) ? $config['CommonUtils\Logger'] : []);

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

        //global catchall to log when a 400 or 500 error message is set on a response.
        //This is mainly for logging purposes.
        $eventManager->attach(
            MvcEvent::EVENT_FINISH,
            function ($e) use ($logger, $extractor, $serviceManager) {
                if (!method_exists($e->getResponse(), 'getStatusCode')) {
                    return;
                }
                $statusCode = $e->getResponse()->getStatusCode();
                if ($statusCode >= 500) {
                    $e->getResponse()->setStatusCode($statusCode);
                    $extractor->setResponse($e->getResponse());
                    $serviceManager->get('Logger')->crit(
                        'Response: ' . $statusCode . '[' . $e->getResponse() . ']',
                        array(
                            'category' => 'Event',
                        )
                    );
                }
                if ($statusCode >= 400 && $statusCode < 500) {
                    $e->getResponse()->setStatusCode($statusCode);
                    $extractor->setResponse($e->getResponse());
                    $serviceManager->get('Logger')->warn(
                        'Response: ' . $statusCode . '[' . $e->getResponse() . ']',
                        array(
                            'category' => 'Event',
                        )
                    );
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
            )
        );
    }
}
