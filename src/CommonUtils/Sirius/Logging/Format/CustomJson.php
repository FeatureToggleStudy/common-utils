<?php

namespace CommonUtils\Sirius\Logging\Format;

use Zend\Log\Formatter\FormatterInterface;
use DateTime;

class CustomJson implements FormatterInterface
{
    /**
     * Get the format specifier for DateTime objects
     * @return string
     */
    public function getDateTimeFormat() {}

    /**
     * @param string $dateTimeFormat
     * @return void|FormatterInterface
     */
    public function setDateTimeFormat($dateTimeFormat) {}

    /**
     * @param array $event
     * @return string
     */
    public function format($event)
    {
        $formattedLogLine = array('timestamp' => '');

        foreach($event as $eventKey => $eventItem) {
            if(is_string($eventItem) || is_int($eventItem)) {
                $formattedLogLine[$eventKey] = $eventItem;
            } else if(is_object($eventItem)) {
                if($eventItem instanceof DateTime) {
                    $formattedLogLine['timestamp'] = $this->extractDateAsTimestamp($eventItem);
                }
            } else if(is_array($eventItem)) {
                foreach($eventItem as $fieldKey => $fieldName) {
                    $formattedLogLine[$eventKey][$fieldKey] = $fieldName;
                }
            }
        }
        return json_encode($formattedLogLine);
    }

    /**
     * @param DateTime $dateTime
     * @return int
     */
    private function extractDateAsTimestamp(DateTime $dateTime)
    {
        return $dateTime->getTimestamp();
    }

}
