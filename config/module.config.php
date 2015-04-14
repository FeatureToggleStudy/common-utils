<?php

return array(
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
            'CommonUtils\Sirius\Controller\LoggerRest' => 'CommonUtils\Sirius\Controller\LoggerRestController'
        ),
    ),
);
