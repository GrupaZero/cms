<?php namespace Gzero\Core;

use Gzero\Core\Events\ContentRouteMatched;
use Gzero\Entity\Content;
use Gzero\Repository\ContentRepository;
use Illuminate\Auth\Access\Gate;
use Illuminate\Events\Dispatcher;
use Gzero\Entity\Lang;
use Gzero\Core\Handler\Content\ContentTypeHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class DynamicRouter
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class DynamicRouter {

    /**
     * @var ContentRepository
     */
    private $repository;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * DynamicRouter constructor
     *
     * @param ContentRepository $repository Content repository
     * @param Dispatcher        $events     Events dispatcher
     * @param Gate              $gate       Gate
     */
    public function __construct(ContentRepository $repository, Dispatcher $events, Gate $gate)
    {
        $this->repository = $repository;
        $this->events     = $events;
        $this->gate       = $gate;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param String  $url     Url address
     * @param Lang    $lang    Lang entity
     * @param Request $request Request
     *
     * @throws NotFoundHttpException
     * @return View
     */
    public function handleRequest($url, Lang $lang, Request $request)
    {
        //Get url without query string, so that pagination can work
        $url     = preg_replace('/\?.*/', '', $url);
        $content = $this->repository->getByUrl($url, $lang->code);
        // Only if page is visible on public
        if (empty($content) || !$this->canBeShown($content)) {
            throw new NotFoundHttpException();
        }

        if (!$content->canBeShown()) {
            app('session')->flash(
                'messages',
                [
                    [
                        'code' => 'warning',
                        'text' => trans('common.content_not_published')
                    ]
                ]
            );
        }
        $this->events->fire(new ContentRouteMatched($content, $request));
        $type = $this->resolveType($content->type);
        return $type->load($content, $lang)->render();
    }

    /**
     * Dynamically resolve type of content
     *
     * @param String $typeName Type name
     *
     * @return ContentTypeHandler
     * @throws \ReflectionException
     */
    protected function resolveType($typeName)
    {
        $type = app()->make('content:type:' . $typeName);
        if (!$type instanceof ContentTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement ContentTypeInterface");
        }
        return $type;
    }

    /**
     * It checks if specified content can be shown for logged user
     *
     * @param Content $content Content
     *
     * @return bool
     */
    protected function canBeShown(Content $content)
    {
        return $content->canBeShown() || (!$content->canBeShown() && $this->gate->denies('XYZ', $content));
    }
}
