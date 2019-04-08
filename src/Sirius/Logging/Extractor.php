<?php

namespace CommonUtils\Sirius\Logging;

class Extractor
{

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function process()
    {
        $extractions = array();
        foreach($this->config as $objectExtractions) {
            $values = $this->extractValuesFromObject($objectExtractions);
            $extractions = array_merge($extractions, $values);
        }
        return $extractions;
    }

    /**
     * @param $classProperty
     * @param $methodsCallsOnClassProperty
     */
    private function extractValuesFromObject($objectExtractions) {

        $values = array();
        $thisClassObject = $objectExtractions['property'];
        $classMethod     = $objectExtractions['method_name'];
        $methodValues    = $objectExtractions['method_values'];
        //does the property exist on the current class
        if(property_exists($this, $thisClassObject)) {
            if(method_exists($this->{$thisClassObject}, $classMethod)) {
                //if it does call the method on the class with a value
                if(is_array($methodValues)) {
                    foreach($methodValues as $value) {
                        $key = strtolower($value);
                        $values[$key] = $this->{$thisClassObject}->$classMethod($value);
                    }
                } else {
                    $values[$methodValues] = $this->{$thisClassObject}->$classMethod();
                }
            }
        }
        return $values;
    }

}