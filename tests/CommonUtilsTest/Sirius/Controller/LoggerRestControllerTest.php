<?php

namespace CommonUtilsTest\Sirius\Controller;

use ApplicationTest\Controller\BaseControllerTestCase;
use CommonUtils\Sirius\Logging\Format\CustomJson;
use CommonUtils\Sirius\Logging\Logger;
use Zend\Log\Filter\Priority;
use Zend\Log\Writer\Mock as MockWriter;

class LoggerRestControllerTest extends BaseControllerTestCase
{
    private $logger;
    private $writer;
    private $formatter;

    public function setUp()
    {
        $this->logger = new Logger();
        $this->writer = new MockWriter();
        $this->formatter = new CustomJson();

        $this->logger->addWriter($this->writer);
        $this->writer->setFormatter($this->formatter);
        $this->writer->addFilter(new Priority(Logger::DEBUG));

        $sm = $this->getApplicationServiceLocator();
        $sm->setAllowOverride(true);
        $sm->setService('CommonUtils\Logger', $this->logger);

        $this->setUpWithUser('a manager', 'manager@opgtest.com');
    }

    public function testLogging()
    {
        $this->dispatch('/api/health-check', 'POST', $this->jsonLog());
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('CommonUtils');
        $this->assertControllerName('CommonUtils\Sirius\Controller\LoggerRest');
        $this->assertControllerClass('LoggerRestController');
        $this->assertMatchedRouteName('health-check');
        $this->assertEquals($this->logEntry(), $this->writer->entries[0]);
    }

    private function jsonLog()
    {
        return '{
          "type": "exception",
          "priority": 20,
          "message": "TypeError: obj.a is not a function",
          "timestamp": "2015-04-28T10:29:14.235Z",
          "url": "http://localhost:8081/#/home",
          "userId": "manager@opgtest.com",
          "stackTrace": [
            "{anonymous}() (h.)$scope.save [as saveAction]@http://localhost:8081/core/js/action-widget/timeline/createPerson.ctrl.js:44:17",
            "{anonymous}() (h.controller.)$scope.save@http://localhost:8081/modules/multi-page-form/multiPageForm.directive.js:148:26",
            "{anonymous}() (h.controller.)$scope.saveAndExit@http://localhost:8081/modules/multi-page-form/multiPageForm.directive.js:222:26",
            "gb.functionCall@http://localhost:8081/bower_components/angular/angular.min.js:178:68",
            "jc.(anonymous function).compile.d.on.f@http://localhost:8081/bower_components/angular/angular.min.js:195:177",
            "{anonymous}() (h.)$get.h.$eval@http://localhost:8081/bower_components/angular/angular.min.js:113:32",
            "{anonymous}() (h.)$get.h.$apply@http://localhost:8081/bower_components/angular/angular.min.js:113:310",
            "HTMLButtonElement.<anonymous>@http://localhost:8081/bower_components/angular/angular.min.js:195:229",
            "HTMLButtonElement.x.event.dispatch@http://localhost:8081/bower_components/jquery/jquery.min.js:5:10006",
            "HTMLButtonElement.x.event.add.y.handle@http://localhost:8081/bower_components/jquery/jquery.min.js:5:6796"
          ],
          "cause": ""
        }';
    }

    private function logEntry()
    {
        return '{
  "timestamp": 1430218033,
  "@fields": {
    "priority": 20,
    "priorityName": "CRIT",
    "message": "TypeError: obj.a is not a function",
    "type": "exception",
    "timestamp": "2015-04-28T10:29:14.235Z",
    "url": "http:\/\/localhost:8081\/#\/home",
    "userId": "manager@opgtest.com",
    "stackTrace": [
      "{anonymous}() (h.)$scope.save [as saveAction]@http:\/\/localhost:8081\/core\/js\/action-widget\/timeline\/createPerson.ctrl.js:44:17",
      "{anonymous}() (h.controller.)$scope.save@http:\/\/localhost:8081\/modules\/multi-page-form\/multiPageForm.directive.js:148:26",
      "{anonymous}() (h.controller.)$scope.saveAndExit@http:\/\/localhost:8081\/modules\/multi-page-form\/multiPageForm.directive.js:222:26",
      "gb.functionCall@http:\/\/localhost:8081\/bower_components\/angular\/angular.min.js:178:68",
      "jc.(anonymous function).compile.d.on.f@http:\/\/localhost:8081\/bower_components\/angular\/angular.min.js:195:177",
      "{anonymous}() (h.)$get.h.$eval@http:\/\/localhost:8081\/bower_components\/angular\/angular.min.js:113:32",
      "{anonymous}() (h.)$get.h.$apply@http:\/\/localhost:8081\/bower_components\/angular\/angular.min.js:113:310",
      "HTMLButtonElement.<anonymous>@http:\/\/localhost:8081\/bower_components\/angular\/angular.min.js:195:229",
      "HTMLButtonElement.x.event.dispatch@http:\/\/localhost:8081\/bower_components\/jquery\/jquery.min.js:5:10006",
      "HTMLButtonElement.x.event.add.y.handle@http:\/\/localhost:8081\/bower_components\/jquery\/jquery.min.js:5:6796"
    ],
    "cause": "",
    "status_code": 200,
    "query_string": "",
    "server_name": "front.local",
    "http_host": "front.local:8081",
    "request_method": "POST",
    "request_uri": "\/logging",
    "content_type": "text\/plain;charset=UTF-8",
    "content_length": "1400",
    "remote_addr": "10.0.2.2",
    "remote_port": "33827",
    "server_addr": "10.0.2.15",
    "https": "",
    "app_env": "development",
    "http_user_agent": "Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/42.0.2311.90 Safari\/537.36",
    "http_x_user_id": null,
    "http_content_type": "text\/plain;charset=UTF-8"
  }
}';
    }
}
