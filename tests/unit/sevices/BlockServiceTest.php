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
        Cache::shouldReceive('forget')
            ->once()
            ->with('blocks:filter:public')
            ->andReturnTrue();

        Cache::shouldReceive('forget')
            ->once()
            ->with('blocks:filter:admin')
            ->andReturnTrue();

        $this->tester->assertTrue($this->service->clearBlocksCache());
    }
}
