<?php namespace Gzero\Cms\Services;

use Illuminate\Support\Facades\Cache;

class BlockService {

    /**
     * Clears blocks cache
     *
     * @return bool
     */
    public function clearBlocksCache()
    {
        return Cache::forget('blocks:filter:public') && Cache::forget('blocks:filter:admin');
    }
}
