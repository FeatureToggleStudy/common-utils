<?php

namespace CommonUtils\Sirius\Logging;

use Zend\Log\Processor\ProcessorInterface;

/**
 * Processes an event message according to PSR-3 rules.
 *
 * This processor replaces `{foo}` with the value from `$extra['foo']`.
 *
 * Adapted from https://github.com/zendframework/zend-log/blob/release-2.9.2/src/Processor/PsrPlaceholder.php
 * Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */
class PsrPlaceholder implements ProcessorInterface
{
    /**
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        if (false === strpos($event['message'], '{')) {
            return $event;
        }

        $replacements = [];
        foreach ($event['extra'] as $key => $val) {
            if (is_null($val)
                || is_scalar($val)
                || (is_object($val) && method_exists($val, "__toString"))
            ) {
                $replacements['{'.$key.'}'] = $val;
                continue;
            }

            if (is_object($val)) {
                $replacements['{'.$key.'}'] = '[object '.get_class($val).']';
                continue;
            }

            $replacements['{'.$key.'}'] = '['.gettype($val).']';
        }

        $event['message'] = strtr($event['message'], $replacements);
        return $event;
    }
}
