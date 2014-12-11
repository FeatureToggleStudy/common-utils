<?php
return array(
    'CommonUtils\Logger' => array(

        // will add the $logger object before the current PHP error handler
        'registerErrorHandler' => false, // errors logged to your writers
        'registerExceptionHandler' => false, // exceptions logged to your writers

        // multiple zend writer output & zend priority filters
        'writers' => array(
            'standard-file' => array(
                'adapter' => '\Zend\Log\Writer\Mock',
                'adapterOptions' => array(
                    'output' => 'data/log/application.log', // path to file
                ),
                'formatter' => '\CommonUtils\Sirius\Logging\Format\CustomJson',
                'formatterOptions' => array(),
                'filter' => getenv('APP_ENV') == 'development' ? \Zend\Log\Logger::DEBUG : \Zend\Log\Logger::DEBUG,
                'enabled' => true,
            )
        ),
        'extractions' => array(0 => array('property' => 'response',
                                          'method_name' => 'getStatusCode',
                                          'method_values' => 'status_code'),
                               1 => array('property' => 'request',
                                          'method_name' => 'getServer',
                                          'method_values' => array(
                                          'QUERY_STRING','SERVER_NAME','HTTP_HOST','REQUEST_METHOD',
                                          'REQUEST_URI','QUERY_STRING','CONTENT_TYPE','CONTENT_LENGTH',
                                          'REMOTE_ADDR','REMOTE_PORT','SERVER_ADDR','HTTPS','APP_ENV',
                                          'HTTP_HOST','HTTP_USER_AGENT','HTTP_X_USER_ID','HTTP_CONTENT_TYPE')),
                              ),

    )
);
