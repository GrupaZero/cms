<?php namespace Gzero\Cms\Services;

class BlockService {

    /**
     * Clears blocks cache
     *
     * @return bool
     */
    public function clearBlocksCache()
    {
        return cache()->tags(['blocks'])->flush();
    }
}
