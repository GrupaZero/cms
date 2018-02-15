<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

/**
 * Class NestedContentController
 *
 */
class NestedContentController extends ApiController {

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
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/contents/{id}/children",
     *   tags={"content"},
     *   summary="List of all nested contents for particular parent",
     *   description="List of all available contents for particular parent",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of parent.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Type to filter by",
     *     required=false,
     *     type="string",
     *     default="content"
     *   ),
     *   @SWG\Parameter(
     *     name="level",
     *     in="query",
     *     description="Level to filter by",
     *     required=false,
     *     type="integer",
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     name="author_id",
     *     in="query",
     *     description="Author id to filter by",
     *     required=false,
     *     type="integer",
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     name="is_sticky",
     *     in="query",
     *     description="Sticked contents filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="only_published",
     *     in="query",
     *     description="Only published contents filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="only_not_published",
     *     in="query",
     *     description="Only not published contents filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="is_on_home",
     *     in="query",
     *     description="Contents being displayed on homepage filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="is_promoted",
     *     in="query",
     *     description="Promoted contents filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="is_comment_allowed",
     *     in="query",
     *     description="Contents with comments ability filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="published_at",
     *     in="query",
     *     description="Date range to filter by",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     default={"2017-10-01","2017-10-07"},
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="created_at",
     *     in="query",
     *     description="Date range to filter by",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     default={"2017-10-01","2017-10-07"},
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="updated_at",
     *     in="query",
     *     description="Date range to filter by",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     default={"2017-10-01","2017-10-07"},
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Content")),
     *  ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     * @param int|null           $id        Id used for nested resources
     *
     * @return ContentCollection
     */
    public function index(UrlParamsProcessor $processor, $id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('readList', Content::class);

        $processor
            ->addFilter(new StringParser('type'))
            ->addFilter(new NumericParser('level'))
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('only_published'))
            ->addFilter(new BoolParser('only_not_published'))
            ->addFilter(new BoolParser('is_sticky'))
            ->addFilter(new BoolParser('is_on_home'))
            ->addFilter(new BoolParser('is_promoted'))
            ->addFilter(new BoolParser('is_comment_allowed'))
            ->addFilter(new DateRangeParser('published_at'))
            ->addFilter(new DateRangeParser('created_at'))
            ->addFilter(new DateRangeParser('updated_at'))
            ->process($this->request);


        if ($this->request->has('only_published')) {
            $results = $this->repository->getManyPublishedChildren($content, $processor->buildQueryBuilder());
        } elseif ($this->request->has('only_not_published')) {
            $results = $this->repository->getManyNotPublishedChildren($content, $processor->buildQueryBuilder());
        } else {
            $results = $this->repository->getManyChildren($content, $processor->buildQueryBuilder());
        }

        $results->setPath(apiUrl('contents/{id}/children', ['id' => $id]));

        return new ContentCollection($results);
    }

}
