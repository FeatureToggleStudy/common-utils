<?php

namespace CommonUtils\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LoggerRestController extends AbstractActionController
{
    public function indexAction()
    {
        //$logJson = json_decode($this->getRequest()->getContent());
        //$logger = $this->getServiceLocator()->get('Logger');
        $response = $this->getResponse();
        $response->setStatusCode(200);
        return $response;
    }
}