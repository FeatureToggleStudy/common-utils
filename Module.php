<?php

namespace CommonUtils;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Log\Filter\Priority;
use Sirius\Logging\Logger;
use Sirius\Logging\Extractor;
use Zend\Log\Logger as ZendLogger;


/**
 * Class Module
 * @package DateUtils
 */
class Module
{
    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $event)
    {
        $eventManager        = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $extractor = $event->getApplication()->getServiceManager()->get('CommonUtils\Extractor');
        $extractor->setRequest($event->getRequest());
        $extractor->setResponse($event->getResponse());

        $logger = $event->getApplication()->getServiceManager()->get('Logger');
        $logger->setExtractor($extractor);

        //catches exceptions for errors during dispatch
        $sharedManager = $event->getApplication()->getEventManager()->getSharedManager();
        $sm = $event->getApplication()->getServiceManager();
        $sharedManager->attach(
            'Zend\Mvc\Application',
            'dispatch.error',
            function ($event) use ($sm) {
                if ($event->getParam('exception')) {
                    $sm->get('Logger')->crit(
                        $event->getParam('exception'),
                        array(
                            'category' => 'Dispatch',
                            'stackTrace' => debug_backtrace(),
                        )
                    );
                }
            }
        );

        //catches exceptions in application code that are uncaught. For example if a database server goes down.
        set_exception_handler(function($exception) use ($sm, $extractor, $event) {
                //ensure that the application log, logs a 500 error
                $event->getResponse()->setStatusCode(500);
                $extractor->setResponse($event->getResponse());
                http_response_code(500);
                $sm->get('Logger')->crit(
                    'Uncaught Exception: [' . $exception->getMessage() . ']',
                    array(
                        'category' => 'Uncaught',
                        'stackTrace' => $exception->getTrace(),
                    )
                );
        });

        //global catchall to log when a 400 or 500 error message is set on a response.
        //This is mainly for logging purposes.
        $eventManager->attach(MvcEvent::EVENT_FINISH,
            function ($e) use ($logger, $extractor) {
                if(!method_exists($e->getResponse(),'getStatusCode')) {
                    return;
                }
                $statusCode = $e->getResponse()->getStatusCode();
                $sm = $e->getApplication()->getServiceManager();
                if($statusCode >= 500) {
                    $e->getResponse()->setStatusCode($statusCode);
                    $extractor->setResponse($e->getResponse());
                    $sm->get('Logger')->crit(
                        'Uncaught Error 500 Exception: [' . $e->getResponse() . ']',
                        array(
                            'category' => 'Uncaught',
                            'stackTrace' => debug_backtrace(),
                        )
                    );
                }
                if($statusCode >= 400 && $statusCode < 500) {
                    $e->getResponse()->setStatusCode($statusCode);
                    $extractor->setResponse($e->getResponse());
                    $sm->get('Logger')->error(
                        'Uncaught Error: [' . $e->getResponse() . ']',
                        array(
                            'category' => 'Uncaught',
                            'stackTrace' => debug_backtrace(),
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
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'CommonUtils\SiriusLogger' => function ($sm) {
                    $config = $sm->get('Config')['CommonUtils\Logger'];
                    $logger = new Sirius\Logging\Logger();

                    foreach ($config['writers'] as $writer) {
                        if ($writer['enabled']) {
                            $writerAdapter = new $writer['adapter']($writer['adapterOptions']['output']);
                            $logger->addWriter($writerAdapter);

                            if(!empty($writer['formatter'])) {
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

                    ZendLogger::registerErrorHandler($logger);
                    return $logger;
                },
                'CommonUtils\SiriusFrontEndLogger' => function ($sm) {
                    return new Sirius\Logging\FrontEndLogger($sm->get('CommonUtils\SiriusLogger'));
                },
                'CommonUtils\Extractor' => function ($sm) {
                    $config = $sm->get('Config')['CommonUtils\Logger']['extractions'];
                    $extractor = new Sirius\Logging\Extractor($config);
                    return $extractor;
                }
            )
        );
    }


}
