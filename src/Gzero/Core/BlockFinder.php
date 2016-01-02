<?php namespace Gzero\Core;

use Gzero\Entity\Block;
use Gzero\Repository\BlockRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Collection;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockFinder
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class BlockFinder {

    /**
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * BlockFinder constructor
     *
     * @param BlockRepository $block Block repository
     * @param CacheManager    $cache Cache
     */
    public function __construct(BlockRepository $block, CacheManager $cache)
    {
        $this->blockRepository = $block;
        $this->cache           = $cache;
    }

    /**
     * It returns blocks ids that should be displayed on specified content
     *
     * @param string $contentPath Content path
     * @param bool   $isPublic    Trigger to display only public blocks
     *
     * @return array
     */
    public function getBlocksIds($contentPath, $isPublic = false)
    {
        return $this->findBlocksForPath($contentPath, $this->buildFilterArray($isPublic));
    }

    /**
     * Find all blocks ids that should be displayed on specific path
     *
     * @param string $path   Content path or static page named route
     * @param array  $filter Array with all filters
     *
     * @return array
     */
    protected function findBlocksForPath($path, $filter)
    {
        if ($this->isStaticPage($path)) { // static page like "home", "contact" etc.
            return $this->handleStaticPageCase($path, $filter);
        }
        $ids      = explode('/', rtrim($path, '/'));
        $idsCount = count($ids);
        $blockIds = [];
        if ($idsCount === 1) { // Root case
            $rootPath = $ids[0] . '/';
            if (isset($filter['paths'][$rootPath])) {
                $blockIds = array_keys($filter['paths'][$rootPath], true, true);
            }
        } else {
            $allIds     = [];
            $parentPath = '';
            foreach ($ids as $index => $id) {
                $parentPath .= $id . '/';
                $pathMatch = ($index + 1 < $idsCount) ? $parentPath . '*' : $parentPath;
                if (isset($filter['paths'][$pathMatch])) {
                    // Order of operation is important! We want to override $allIds be lower level filter (left overrides right)
                    $allIds = $filter['paths'][$pathMatch] + $allIds;
                }
            }
            // We're returning only blocks ids that uses filter property
            $blockIds = array_keys($allIds + $filter['excluded'], true, true);
        }
        return $blockIds;
    }

    /**
     * It builds filter array with all blocks
     *
     * @param bool $isPublic Filter only public blocks
     *
     * @return array
     */
    protected function buildFilterArray($isPublic)
    {
        $cacheKey = ($isPublic) ? 'public' : 'admin';
        if ($this->cache->has('blocks:filter:' . $cacheKey)) {
            return $this->cache->get('blocks:filter:' . $cacheKey);
        } else {
            if ($isPublic) {
                $blocks = $this->blockRepository->getBlocks(
                    [['filter', '!=', null], ['isActive', '=', true]],
                    [['weight', 'ASC']],
                    null,
                    null
                );
            } else {
                $blocks = $this->blockRepository->getBlocks([['filter', '!=', null]], [['weight', 'ASC']], null, null);
            }
            $filter = $this->extractFilterProperty($blocks);
            $this->cache->forever('blocks:filter:' . $cacheKey, $filter);
            return $filter;
        }
    }

    /**
     * It extracts all filters from blocks & build filter array
     *
     * @param array|Collection $blocks Blocks
     *
     * @return array
     */
    protected function extractFilterProperty($blocks)
    {
        $filter   = [];
        $excluded = [];
        foreach ($blocks as $block) {
            $this->extractFilter($block, $filter, $excluded);
        }
        return ['paths' => $filter, 'excluded' => $excluded];
    }

    /**
     * It checks if we're  dealing with static page
     *
     * @param string $path named route alias
     *
     * @return bool
     */
    protected function isStaticPage($path)
    {
        return !preg_match('/\//', $path);
    }

    /**
     * It checks which blocks should be displayed for specific static page
     *
     * @param string $path   Static page named route
     * @param array  $filter Array with all filters
     *
     * @return array
     */
    protected function handleStaticPageCase($path, $filter)
    {
        if (isset($filter['paths'][$path])) {
            return array_keys($filter['paths'][$path] + $filter['excluded'], true, true);
        }
        return array_keys($filter['excluded'], true, true);
    }

    /**
     * It extracts filter property from single block
     *
     * @param Block $block    Block instance
     * @param array $filter   Filter array
     * @param array $excluded Excluded array
     *
     * @return void
     */
    protected function extractFilter(Block $block, Array &$filter, Array &$excluded)
    {
        if (isset($block->filter['+'])) {
            foreach ($block->filter['+'] as $filterValue) {
                if (isset($filter[$filterValue])) {
                    $filter[$filterValue][$block->id] = true;
                } else {
                    $filter[$filterValue] = [$block->id => true];
                }
            }
        }
        if (isset($block->filter['-'])) {
            foreach ($block->filter['-'] as $filterValue) {
                if (isset($filter[$filterValue])) {
                    $filter[$filterValue][$block->id] = false;
                } else {
                    $filter[$filterValue] = [$block->id => false];
                }
                // By default block with - should display on all sited except those from filter
                $excluded[$block->id] = true;
            }
        }
    }
}
