<?php

namespace CommonUtils\Sirius\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LoggerRestController extends AbstractActionController
{
    public function indexAction()
    {
        $this->getServiceLocator()->get('CommonUtils\SiriusFrontEndLogger')->log($this->getRequest()->getContent());
        return $this->getResponse();
    }
}
