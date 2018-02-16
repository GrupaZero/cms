<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Services\BlockService;
use Illuminate\Support\Facades\Cache;

class BlockServiceTest extends Unit {

    /** @var \Cms\UnitTester */
    protected $tester;

    /** @var BlockService */
    protected $service;

    protected function _before()
    {
        $this->service = resolve(BlockService::class);
    }

    /** @test */
    public function canClearBlocksCache()
    {
        Cache::shouldReceive('tags')
            ->once()
            ->with(['blocks'])
            ->andReturnSelf();

        Cache::shouldReceive('flush')
            ->once()
            ->andReturnTrue();

        $this->tester->assertTrue($this->service->clearBlocksCache());
    }
}
