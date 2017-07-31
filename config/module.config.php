<?php

use CommonUtils\Sirius\Controller\CacheWarmerController;
use CommonUtils\Sirius\Controller\Factory\CacheWarmerControllerFactory;

return array(
    'console' => [
        'router' => [
            'routes' => [
                'moj/common-utils/sirius/cache-warmup' => [
                    'options' => [
                        'route' => 'sirius cache:warmup',
                        'defaults' => [
                            'controller' => CacheWarmerController::class,
                            'action' => 'index',
                        ],
                    ],
                ]
            ],
        ],
    ],
    'router' => array(
        'routes' => array(
            'log' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/logging',
                    'verb'     => 'post',
                    'defaults' => array(
                        '__NAMESPACE__' => 'CommonUtils\Sirius\Controller',
                        'controller'    => 'CommonUtils\Sirius\Controller\LoggerRest',
                        'action'        => 'index'
                    ),
                ),
            ),
        ),
    ),
    'controllers'     => array(
        'invokables' => array(
            'CommonUtils\Sirius\Controller\LoggerRest' => 'CommonUtils\Sirius\Controller\LoggerRestController',
        ),
        'factories' => [
            CacheWarmerController::class => CacheWarmerControllerFactory::class,
        ],
    ),
);
