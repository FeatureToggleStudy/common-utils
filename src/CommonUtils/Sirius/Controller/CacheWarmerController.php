<?php

namespace CommonUtils\Sirius\Controller;

use CommonUtils\Sirius\CacheWarmer\CacheWarmer;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractActionController;

class CacheWarmerController extends AbstractActionController
{
    /**
     * @var array
     */
    private $cacheWarmers;

    /**
     * @var Console
     */
    private $console;

    /**
     * CacheWarmerController constructor.
     *
     * @param Console $console
     * @param array|CacheWarmer[] $cacheWarmers
     */
    public function __construct(Console $console, array $cacheWarmers)
    {
        $this->console = $console;
        $this->cacheWarmers = $cacheWarmers;
    }

    public function indexAction()
    {
        $total = count($this->cacheWarmers);
        $this->console->writeLine(sprintf('Executing cache warmers (%d)...', $total), ColorInterface::GREEN);

        $i = 1;
        foreach ($this->cacheWarmers as $cacheWarmer) {
            $this->console->writeLine(PHP_EOL .
                sprintf('[%02d/%02d] %s', $i, $total, $cacheWarmer->getName()),
                ColorInterface::YELLOW
            );
            $this->console->writeLine('        ' . $cacheWarmer->getDescription(), ColorInterface::YELLOW);
            $cacheWarmer->warmUp();
            $i++;
        }

        $this->console->writeLine(PHP_EOL . 'Done.', ColorInterface::GREEN);
    }
}
