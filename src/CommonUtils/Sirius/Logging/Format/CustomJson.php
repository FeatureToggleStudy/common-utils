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
        $log = $event;
        unset($log['extra']);
        $log = array_merge($log, $event['extra']);
        $log['timestamp'] = $this->extractDateAsTimestamp($event['timestamp']);

        return json_encode($log);
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
