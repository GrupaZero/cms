<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Query\QueryBuilder;
use Illuminate\Http\Request;

/**
 * Class PublicContentController
 *
 * @SWG\Tag(
 *   name="content",
 *   description="Everything about deleted app content"
 *   )
 */
class PublicContentController extends ApiController {

    /** @var ContentReadRepository */
    protected $repository;

    /** @var Request */
    protected $request;

    /**
     * ContentController constructor.
     *
     * @param ContentReadRepository $repository Content repository
     * @param Request               $request    Request object
     */
    public function __construct(ContentReadRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display list of public contents
     *
     * @SWG\Get(
     *   path="/public-contents",
     *   tags={"content, public"},
     *   summary="List of public contents",
     *   description="List of all publicly accessible contents",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Content")),
     *  ),
     * )
     *
     * @return ContentCollection
     */
    public function index()
    {
        $results = $this->repository->getManyPublished(new QueryBuilder());
        $results->setPath(apiUrl('public-contents'));

        return new ContentCollection($results);
    }
}
