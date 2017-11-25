<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\DateRangeParser;
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
     *   tags={"content", "public"},
     *   summary="List of all nested contents for particular parent",
     *   description="List of all available contents for particular parent",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
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
     * @param int|null           $id        Id used for nested resources
     *
     * @return ContentCollection
     */
    public function index(UrlParamsProcessor $processor, $id)
    {
        $content = $this->repository->getById($id);
        if (empty($content)) {
            return abort(404);
        }

        $processor
            ->addFilter(new StringParser('language'))
            ->addFilter(new StringParser('type'))
            ->addFilter(new StringParser('is_active'))
            ->addFilter(new StringParser('level'))
            ->addFilter(new StringParser('trashed'))
            ->addFilter(new DateRangeParser('created_at'))
            ->process($this->request);

        $results = $this->repository->getManyChildren($content, $processor->buildQueryBuilder());
        $results->setPath(apiUrl('contents/{id}/children', ['id' => $id]));

        return new ContentCollection($results);
    }

}
