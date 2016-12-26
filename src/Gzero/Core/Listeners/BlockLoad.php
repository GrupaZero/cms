<?php namespace Gzero\Core\Listeners;

use Gzero\Core\BlockFinder;
use Gzero\Core\Events\ContentRouteMatched;
use Gzero\Repository\BlockRepository;
use Gzero\Repository\LangRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Events\RouteMatched;

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
    protected $blockFinder;

    /**
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * @var LangRepository
     */
    protected $langRepository;

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
     * @param mixed $event Matched route or content
     *
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof RouteMatched) {
            $this->handleLaravelRoute($event);
        }

        if ($event instanceof ContentRouteMatched) {
            $this->handleContentRoute($event);
        }
    }

    /**
     * Handle the event. It loads block for static named routes.
     *
     * @param RouteMatched $event dispatched event
     *
     * @return void
     *
     */
    public function handleLaravelRoute(RouteMatched $event)
    {
        if ($event->request->method() === 'GET' && $event->route->domain() === env('DOMAIN') && $event->route->getName()) {
            $blockIds = $this->blockFinder->getBlocksIds($event->route->getName(), true);
            $blocks   = $this->blockRepository->getVisibleBlocks($blockIds, true);
            $this->handleBlockRendering($blocks);
            $blocks = $blocks->groupBy('region');
            view()->share('blocks', $blocks);
            view()->share('sidebarsNumber', $this->getSidebarsNumber($blocks));
        }
    }

    /**
     * Handle the event. It loads block for dynamic router.
     *
     * @param ContentRouteMatched $event dispatched event
     *
     * @return void
     *
     */
    public function handleContentRoute(ContentRouteMatched $event)
    {
        $blockIds = $this->blockFinder->getBlocksIds($event->content->path, true);
        $blocks   = $this->blockRepository->getVisibleBlocks($blockIds, true);
        $this->handleBlockRendering($blocks);
        $blocks = $blocks->groupBy('region');
        view()->share('blocks', $blocks);
        view()->share('sidebarsNumber', $this->getSidebarsNumber($blocks));
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

    /**
     * It gets number of active sidebars regions
     *
     * @param Collection $blocks List of blocks
     *
     * @return int number of active sidebars
     */
    protected function getSidebarsNumber($blocks)
    {
        $sidebarsNumber = 0;
        foreach (['sidebarLeft', 'sidebarRight'] as $region) {
            if ($blocks->has($region)) {
                $sidebarsNumber++;
            }
        }
        return $sidebarsNumber;
    }

}
