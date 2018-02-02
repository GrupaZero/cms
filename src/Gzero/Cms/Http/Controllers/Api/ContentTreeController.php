<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;

/**
 * Class NestedContentController
 *
 */
class ContentTreeController extends ApiController {

    /** @var ContentReadRepository */
    protected $repository;

    /** @var ContentValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * ContentController constructor.
     *
     * @param ContentReadRepository $repository Content repository
     * @param ContentValidator      $validator  Content validator
     * @param Request               $request    Request object
     */
    public function __construct(ContentReadRepository $repository, ContentValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }


    /**
     * Fetches all content in a tree structure
     *
     * @SWG\Get(
     *   path="/contents-tree",
     *   tags={"content"},
     *   summary="Fetches all content in a tree structure",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="only_category",
     *     in="query",
     *     description="Should fetch only categories?",
     *     required=false,
     *     type="boolean",
     *     default="false"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Successful operation",
     *      @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Content"))
     *   ),
     * ),
     *
     * @param UrlParamsProcessor $processor params processor
     *
     * @return string
     */
    public function index(UrlParamsProcessor $processor)
    {
        $this->authorize('readList', Content::class);

        // @TODO Implement contents/{id}/tree
        $processor
            ->addFilter(new BoolParser('only_categories'))
            ->process($this->request);

        Resource::wrap('data');
        return (new ContentCollection($this->repository->getTree($processor->buildQueryBuilder())));
    }

}
