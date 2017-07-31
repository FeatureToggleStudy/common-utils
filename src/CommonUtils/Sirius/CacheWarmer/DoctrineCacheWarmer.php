<?php

namespace CommonUtils\Sirius\CacheWarmer;

/**
 * (Re)generates the proxy classes used by Doctrine.
 */
class DoctrineCacheWarmer implements CacheWarmer
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'DoctrineCacheWarmer';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Re-generates the proxy classes used by Doctrine';
    }

    /**
     * @return void
     */
    public function warmUp()
    {
        passthru(sprintf('php %s/public/index.php orm:generate-proxies', getcwd()));
    }
}
