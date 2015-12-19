<?php namespace Gzero\Core;

use Gzero\Entity\Block;
use Gzero\Repository\BlockRepository;

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
     * BlockFinder constructor
     *
     * @param BlockRepository $block Block repository
     */
    public function __construct(BlockRepository $block)
    {
        $this->blockRepository = $block;
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
        return $this->findBlocksForContent($contentPath, $this->buildFilterArray($isPublic));
    }

    /**
     * Find all blocks ids that should be displayed on specific path
     *
     * @param string $path   Content path or static page named route
     * @param array  $filter Array with all filters
     *
     * @return array
     */
    protected function findBlocksForContent($path, $filter)
    {
        if ($this->isStaticPage($path)) { // static page like "home", "contact" etc.
            return $this->handleStaticPageCase($path, $filter);
        }
        $ids      = explode('/', rtrim($path, '/'));
        $idsCount = count($ids);
        $blockIds = [];
        if ($idsCount === 1) { // Root case
            $rootPath = $ids[0] . '/';
            if (isset($filter[$rootPath])) {
                $blockIds = array_keys($filter[$rootPath], true, true);
            }
        } else {
            $allIds     = [];
            $parentPath = '';
            foreach ($ids as $index => $id) {
                $parentPath .= $id . '/';
                $pathMatch = ($index + 1 < $idsCount) ? $parentPath . '*' : $parentPath;
                if (isset($filter[$pathMatch])) {
                    // Order of operation is important! We want to override $allIds be lower level filter (left overrides right)
                    $allIds = $filter[$pathMatch] + $allIds;
                }
            }
            $blockIds = array_keys($allIds, true, true); // We're returning only blocks ids with "+" for this site
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
        $filter = [];
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
        foreach ($blocks as $block) {
            $filter = $this->extractFilterProperty($block, $filter);
        }
        // @TODO Add cache
        return $filter;
    }

    /**
     * It extracts all filters from single block
     *
     * @param Block $block  Block
     * @param array $filter Array with all filters
     *
     * @return array
     */
    protected function extractFilterProperty(Block $block, $filter)
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
            }
            return $filter;
        }
        return $filter;
    }

    /**
     * It checks if we're  dealing with static page
     *
     * @param string $path named route alias
     *
     * @return bool
     */
    private function isStaticPage($path)
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
    private function handleStaticPageCase($path, $filter)
    {
        if (isset($filter[$path])) {
            return array_keys($filter[$path], true, true);
        }
        return [];
    }
}
