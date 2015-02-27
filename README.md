common-utils
============

A Zend Framework 2 module which currently does logging. The idea with this was to perhaps create a shared library that
 was needed across front-end,back-end,membrane. The requirements for the logger will built from this ticket in JIRA: https://opgtransform.atlassian.net/browse/SDV-309. Any update to this library means that you need to run composer for all three repos to update the logging for it. 
 
 There is a sample configuration file for the logger contained [in this repo](https://github.com/ministryofjustice/common-utils/blob/master/config/sample.logger.global.php)
 logger.global.php needs to be in the repo where you want logging. An example is [here](https://github.com/ministryofjustice/opg-core-back-end/blob/master/config/autoload/logger.global.php) 

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
                               
  
  The logger is configurable. There is a [Extractor Class](https://github.com/ministryofjustice/common-utils/blob/master/src/CommonUtils/Sirius/Logging/Extractor.php) 
  which has the request and response objects set on it. The configurations then can extract variables based on configuration.
  
  property: Is either response or request. 
  
  method_name : the method to call on the property.
  
  method_values : the method value to pass to the method. 
