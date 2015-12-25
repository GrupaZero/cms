<?php namespace Gzero\Core\Listeners\Events;

use Gzero\Core\BlockFinder;
use Gzero\Entity\Content;
use Gzero\Repository\BlockRepository;
use Gzero\Repository\LangRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockLoad
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class BlockLoad {

    /**
     * @var BlockFinder
     */
    private $blockFinder;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var LangRepository
     */
    private $langRepository;

    /**
     * Event constructor.
     *
     * @param BlockFinder     $blockFinder     Block Finder
     * @param BlockRepository $blockRepository Block Repository
     * @param LangRepository  $langRepository  Lang Repository
     */
    public function __construct(BlockFinder $blockFinder, BlockRepository $blockRepository, LangRepository $langRepository)
    {
        $this->blockFinder     = $blockFinder;
        $this->blockRepository = $blockRepository;
        $this->langRepository  = $langRepository;
    }

    /**
     * Handle the event. It loads block for matched routes.
     *
     * @param mixed        $route   Matched route or content
     * @param Request|null $request Request
     *
     * @return void
     */
    public function handle($route, $request = null)
    {
        if ($request) {
            $this->handleLaravelRoute($route, $request);
        } else {
            $this->handleContentRoute($route);
        }
    }

    /**
     * Handle the event. It loads block for static named routes.
     *
     * @param Route   $route   Matched route
     * @param Request $request Request
     *
     * @return void
     */
    public function handleLaravelRoute(Route $route, Request $request)
    {
        if ($request->method() === 'GET' && $route->domain() === env('DOMAIN') && $route->getName()) {
            $blockIds = $this->blockFinder->getBlocksIds($route->getName());
            $blocks   = $this->blockRepository->getVisibleBlocks($blockIds);
            $this->handleBlockRendering($blocks);
            view()->share('blocks', $blocks->groupBy('region'));
        }
    }

    /**
     * Handle the event. It loads block for dynamic router.
     *
     * @param Content $content Content entity
     *
     * @return void
     */
    public function handleContentRoute(Content $content)
    {
        $blockIds = $this->blockFinder->getBlocksIds($content->path);
        $blocks   = $this->blockRepository->getVisibleBlocks($blockIds);
        $this->handleBlockRendering($blocks);
        view()->share('blocks', $blocks->groupBy('region'));
    }

    /**
     * It renders blocks
     *
     * @param Collection $blocks List of blocks to render
     *
     * @return void
     */
    protected function handleBlockRendering($blocks)
    {
        foreach ($blocks as &$block) {
            $type        = app()->make('block:type:' . $block->type);
            $block->view = $type->load($block, $this->langRepository->getCurrent())->render();
        }
    }

}
