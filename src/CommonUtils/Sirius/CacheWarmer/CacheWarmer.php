<?php

namespace CommonUtils\Sirius\CacheWarmer;

interface CacheWarmer
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return void
     */
    public function warmUp();
}
