<?php

namespace CommonUtils\Sirius\Logging;

use Zend\Log\Logger as ZendLogger;

class Logger extends ZendLogger
{
    /**
     * @param int $priority
     * @param mixed $message
     * @param array $extra
     * @return ZendLogger
     */
    public function log($priority, $message, $extra = array())
    {
        $extractions = $this->extractDataFromRequestResponse();
        return parent::log($priority, $message, array_merge($extra, $extractions));
    }

    /**
     * @param $extractor
     */
    public function setExtractor($extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * @return array
     */
    private function extractDataFromRequestResponse()
    {
        if (isset($this->extractor)) {
            return $this->extractor->process();
        } else {
            return array('empty' => 'extractor not set');
        }
    }
}
