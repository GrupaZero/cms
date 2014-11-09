<?php namespace Gzero\Core;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Entity\Block;
use Gzero\Entity\Lang;
use Gzero\Repository\BlockRepository;
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

    // @codingStandardsIgnoreStart
    // TODO Refactor this block handler

    private $app;
    private $cache;
    private $blockRepo;
    private $regions;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRegions()
    {
        return $this->regions;
    }

    public function __construct(Cache $cache, Application $app, BlockRepository $blockRepo)
    {
        $this->app       = $app;
        $this->regions   = new ArrayCollection();
        $this->cache     = $cache;
        $this->blockRepo = $blockRepo;
    }

    /**
     * @param      $url
     * @param Lang $lang
     *
     * @return $this
     * @SuppressWarnings("unused")
     */
    public function loadAllActive($url, Lang $lang)
    {
        $regions = [];
        $blocks  = $this->cacheAll($lang);
        foreach ($blocks as $block) {
            if ($this->checkVisibility($block)) {
                // Build not cached blocks
                if (!$block->isCacheable()) {
                    $this->build($block, $lang);
                }
                foreach ($block->getRegions() as $region) {
                    if (empty($regions[$region])) {
                        $regions[$region] = new ArrayCollection();
                    }
                    $regions[$region]->add($block);
                }
            }
        }
        $this->regions = new ArrayCollection($regions);
        return $this;
    }

    protected function cacheAll($lang)
    {
        // if (!$this->cache->has('blocks')) {
        $blocks = $this->blockRepo->getAllActive($lang);
        foreach ($blocks as &$block) {
            // Build only for cacheable blocks
            if ($block->isCacheable()) {
                $this->build($block, $lang);
            }
        }
        // $this->cache->forever('blocks', $blocks); TODO cache on DOCTRINE 2 entity
        return $blocks;
        //} else {
        //  return $this->cache->get('blocks');
        //}
    }

    /**
     * @param      $block
     * @param Lang $lang
     */
    public function build($block, Lang $lang)
    {
        $type        = $this->resolveType($block->getTypeName());
        $block->view = $type->load($block, $lang)->render();
    }

    /**
     * @param $typeName
     *
     * @return mixed
     */
    protected function resolveType($typeName)
    {
        return $this->app->make('block_type:' . $typeName);
    }

    /**
     * Check if block is visible
     *
     * @param Block $block Block entity
     *
     * @return bool
     * @SuppressWarnings("unused")
     */
    protected function checkVisibility($block)
    {
        return true;
    }

    // @codingStandardsIgnoreEND
}
