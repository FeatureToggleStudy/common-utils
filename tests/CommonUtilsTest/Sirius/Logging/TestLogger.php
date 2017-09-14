<?php

namespace CommonUtilsTest\Sirius\Logging;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    /**
     * @var ArrayCollection
     */
    private $stream;

    public function __construct()
    {
        $this->stream = new ArrayCollection();
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->stream->add([
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->stream;
    }
}