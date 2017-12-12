<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

/**
 * Class DeletedContentController
 *
 * @SWG\Tag(
 *   name="content",
 *   description="Everything about deleted app content"
 *   )
 */
class DeletedContentController extends ApiController {

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
     * Display list of soft deleted contents
     *
     * @SWG\Get(
     *   path="/deleted-contents",
     *   tags={"content"},
     *   summary="List of soft deleted contents",
     *   description="List of all soft deleted contents",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="deleted_at",
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
     *  )
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     *
     * @return ContentCollection
     */
    public function index(UrlParamsProcessor $processor)
    {
        $this->authorize('readList', Content::class);

        $processor
            ->addFilter(new DateRangeParser('deleted_at'))
            ->process($this->request);

        $results = $this->repository->getManyDeleted($processor->buildQueryBuilder());
        $results->setPath(apiUrl('deleted-contents'));

        return new ContentCollection($results);
    }
}
