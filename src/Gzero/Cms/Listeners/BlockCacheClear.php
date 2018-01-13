<?php namespace Gzero\Cms\Listeners;

use Gzero\Cms\Services\BlockService;

class BlockCacheClear {

    /**
     * @var BlockService
     */
    protected $blockService;

    /**
     * Event constructor.
     *
     * @param BlockService $blockService Block service
     */
    public function __construct(BlockService $blockService)
    {
        $this->blockService = $blockService;
    }

    /**
     * Handle the event. It clears block cache
     *
     * @return bool
     */
    public function handle()
    {
        return $this->blockService->clearBlocksCache();
    }
}
