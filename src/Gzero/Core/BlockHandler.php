<?php namespace Gzero\Core;

use Gzero\EloquentBaseModel\Model\Collection;
use Gzero\Models\Lang;
use Gzero\Repositories\Block\BlockRepository;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Foundation\Application;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockHandler
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class BlockHandler {

    private $app;
    private $cache;
    private $blockRepo;
    private $regions;

    public function __construct(BlockRepository $block, Cache $cache, Application $app)
    {
        $this->app       = $app;
        $this->regions   = new Collection();
        $this->cache     = $cache;
        $this->blockRepo = $block;
    }

    public function loadAllActive($url, Lang $lang)
    {
        $regions = array();
        $blocks  = $this->cacheAll($lang);
        foreach ($blocks as $block) {
            if ($this->checkVisibility($block)) {
                if (!$block->is_cacheable) { // Build not cached blocks
                    $this->build($block, $lang);
                }
                foreach (explode('|', $block->region) as $region) {
                    if (empty($regions[$region])) {
                        $regions[$region] = new Collection();
                    }
                    $regions[$region]->add($block);
                }
            }
        }
        $this->regions = $this->regions->make($regions);
        \View::share('regions', $this->regions);
    }

    protected function cacheAll($lang)
    {
        if (!$this->cache->has('blocks')) {
            $blocks = $this->blockRepo->getAllActive($lang);
            foreach ($blocks as &$block) {
                if ($block->is_cacheable) { // Build only for cacheable blocks
                    $this->build($block, $lang);
                }
            }
            $this->cache->forever('blocks', $blocks);
            return $blocks;
        } else {
            return $this->cache->get('blocks');
        }
    }

    protected function build($block, Lang $lang)
    {
        $type        = $this->resolveType($block->type->name);
        $block->view = $type->load($block, $lang)->render();
    }

    protected function resolveType($typeName)
    {
        return $this->app->make('block_type:' . $typeName);
    }

    protected function checkVisibility($block)
    {
        return TRUE;
    }
}
