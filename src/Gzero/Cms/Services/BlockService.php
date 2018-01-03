<?php namespace Gzero\Cms\Services;

class BlockService {

    /**
     * Clears blocks cache
     *
     * @return bool
     */
    public function clearBlocksCache()
    {
        return cache()->forget('blocks:filter:public') && cache()->forget('blocks:filter:admin');
    }
}
