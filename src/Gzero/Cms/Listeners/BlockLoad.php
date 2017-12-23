<?php namespace Gzero\Cms\Listeners;

use Gzero\Cms\Services\BlockService;
use Gzero\Cms\BlockFinder;
use Gzero\Core\Events\RouteMatched as GzeroRouteMatched;
use Gzero\Core\Services\LanguageService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Events\RouteMatched;

class BlockLoad {

    /**
     * @var BlockFinder
     */
    protected $blockFinder;

    /**
     * @var BlockService
     */
    protected $blockRepository;

    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * Event constructor.
     *
     * @param BlockFinder     $blockFinder     Block Finder
     * @param BlockService    $blockRepository Block Repository
     * @param LanguageService $langRepository  Lang Repository
     */
    public function __construct(BlockFinder $blockFinder, BlockService $blockRepository, LanguageService $langRepository)
    {
        $this->blockFinder     = $blockFinder;
        $this->blockRepository = $blockRepository;
        $this->languageService = $langRepository;
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

        if ($event instanceof GzeroRouteMatched) {
            $this->handleRoute($event);
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
        }
    }

    /**
     * Handle the event. It loads block for dynamic router.
     *
     * @param GzeroRouteMatched $event dispatched event
     *
     * @return void
     *
     */
    public function handleRoute(GzeroRouteMatched $event)
    {
        $blockIds = $this->blockFinder->getBlocksIds($event->route->path, true);
        $blocks   = $this->blockRepository->getVisibleBlocks($blockIds, true);
        $this->handleBlockRendering($blocks);
        $blocks = $blocks->groupBy('region');
        view()->share('blocks', $blocks);
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
            $type        = resolve($block->type->handler);
            $block->view = $type->handle($block, $this->languageService->getCurrent());
        }
    }
}
