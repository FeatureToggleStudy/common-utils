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
                        '__NAMESPACE__' => 'CommonUtils\Controller',
                        'controller'    => 'CommonUtils\Controller\LoggerRest',
                        'action'        => 'index'
                    ),
                ),
            ),
        ),
    ),
    'controllers'     => array(
        'invokables' => array(
            'CommonUtils/Controller/LoggerRest' => 'CommonUtils/Controller/LoggerRestController'
        ),
    ),
);
