<?php namespace Gzero\Cms;

use Gzero\Cms\Models\Block;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Core\Models\Routable;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Collection;

class BlockFinder {

    /**
     * @var BlockReadRepository
     */
    protected $blockRepository;

    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * BlockFinder constructor
     *
     * @param BlockReadRepository $block Block repository
     * @param CacheManager        $cache Cache
     */
    public function __construct(BlockReadRepository $block, CacheManager $cache)
    {
        $this->blockRepository = $block;
        $this->cache           = $cache;
    }

    /**
     * It returns blocks ids that should be displayed on specified content
     *
     * @param string|Routable $routable   Routable
     * @param bool            $onlyActive Trigger to display only active blocks
     *
     * @return array
     */
    public function getBlocksIds($routable, $onlyActive = false)
    {
        return $this->findBlocksForPath($routable, $this->getFilterArray($onlyActive));
    }

    /**
     * Find all blocks ids that should be displayed on specific path
     *
     * @param string|Routable $routable Routable path or static page named route
     * @param array           $filter   Array with all filters
     *
     * @return array
     */
    protected function findBlocksForPath($routable, $filter)
    {
        if (is_string($routable)) { // static page like "home", "contact" etc.
            return $this->handleStaticPageCase($routable, $filter);
        }
        $ids      = $routable->getTreePath();
        $idsCount = count($ids);
        $blockIds = [];
        if ($idsCount === 1) { // Root case
            $allIds   = [];
            $rootPath = $ids[0] . '/';
            if (isset($filter['paths'][$rootPath])) {
                $allIds = $filter['paths'][$rootPath];
            }
            // We're returning only blocks ids that uses filter property
            $blockIds = array_keys($allIds + $filter['excluded'], true, true);
        } else {
            $allIds     = [];
            $parentPath = '';
            foreach ($ids as $index => $id) {
                $parentPath .= $id . '/';
                $pathMatch  = ($index + 1 < $idsCount) ? $parentPath . '*' : $parentPath;
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
     * @param bool $onlyActive Filter only public blocks
     *
     * @return array
     */
    protected function getFilterArray($onlyActive)
    {
        $cacheKey = ($onlyActive) ? 'public' : 'admin';
        if ($this->cache->has('blocks:filter:' . $cacheKey)) {
            return $this->cache->get('blocks:filter:' . $cacheKey);
        } else {
            $blocks = $this->blockRepository->getBlocksWithFilter($onlyActive);
            $filter = $this->buildFilterArray($blocks);
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
    protected function buildFilterArray($blocks)
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
    protected function extractFilter(Block $block, array &$filter, array &$excluded)
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
