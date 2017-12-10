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
use Illuminate\Support\Collection as LaravelCollection;

/**
 * Class ContentController
 *
 * @SWG\Tag(
 *   name="content",
 *   description="Everything about app content"
 *   )
 */
class ContentController extends ApiController {

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
     *   path="/contents",
     *   tags={"content"},
     *   summary="List of all contents",
     *   description="List of all available contents",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
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
     *     description="Contents with commetns abbility filter",
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
     *  )
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     *
     * @return ContentCollection
     */
    public function index(UrlParamsProcessor $processor)
    {
        // @TODO Can we trigger validation only for is active filter?
        //$this->authorize('readList', Content::class);

        $processor
            ->addFilter(new StringParser('type'))
            ->addFilter(new NumericParser('level'))
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('is_sticky'))
            ->addFilter(new BoolParser('is_on_home'))
            ->addFilter(new BoolParser('is_promoted'))
            ->addFilter(new BoolParser('is_comment_allowed'))
            ->addFilter(new DateRangeParser('published_at'))
            ->addFilter(new DateRangeParser('created_at'))
            ->addFilter(new DateRangeParser('updated_at'))
            ->process($this->request);

        $results = $this->repository->getMany($processor->buildQueryBuilder());
        $results->setPath(apiUrl('contents'));

        return new ContentCollection($results);
    }

    /**
     * Display list of soft deleted contents
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexOfDeleted()
    {
        $this->authorize('readList', Content::class);
        $input  = $this->validator->validate('list');
        $params = $this->processor->process($input)->getProcessedFields();

        $results = $this->repository->getDeletedContents(
            $params['filter'],
            $params['orderBy'],
            $params['page'],
            $params['perPage']
        );

        return $this->respondWithSuccess($results, new ContentTransformer);
    }

    /**
     * Display a listing of the resource as nested tree.
     *
     * @param int|null $id Id used for nested resources
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexTree($id = null)
    {
        $this->authorize('readList', Content::class);
        $input  = $this->validator->validate('tree');
        $params = $this->processor->process($input)->getProcessedFields();
        $this->getSerializer()->parseIncludes('children'); // We need to enable children include to return tree from api
        if ($id) { // Single tree
            $content = $this->repository->getById($id);
            if (!empty($content)) {
                return $this->respondWithSuccess(
                    $this->repository->getTree(
                        $content,
                        $params['filter'],
                        $params['orderBy']
                    ),
                    new ContentTransformer
                );
            } else {
                return $this->respondNotFound();
            }
        }
        // All trees
        //$params['filter'] = array_merge(['type' => ['value' => 'category', 'relation' => null]], $params['filter']);
        $nodes = $this->repository->getContentsByLevel(
            $params['filter'],
            $params['orderBy'],
            null
        );

        $trees = $this->repository->buildTree($nodes);
        // We need to guarantee LaravelCollection here because buildTree will return single root
        // if we have only one
        if (!empty($trees) && !$trees instanceof LaravelCollection) {
            $trees = new LaravelCollection([$trees]);
        }
        return $this->respondWithSuccess($trees, new ContentTransformer);
    }

    /**
     * Display a listing of the resource.
     *
     * @param int|null $contentId Content id for which we are displaying files
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexOfFiles($contentId)
    {
        $this->authorize('readList', File::class);
        $input   = $this->validator->validate('files');
        $params  = $this->processor->process($input)->getProcessedFields();
        $content = $this->repository->getById($contentId);
        if (empty($content)) {
            return $this->respondNotFound();
        }
        $results = $this->fileRepository->getEntityFiles(
            $content,
            $params['filter'],
            $params['orderBy'],
            $params['page'],
            $params['perPage']
        );
        return $this->respondWithSuccess($results, new FileTransformer);
    }

    /**
     * Display a specified resource.
     *
     * @param int $id Id of the resource
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $content = $this->repository->getById($id);
        if (!empty($content)) {
            $this->authorize('read', $content);
            return $this->respondWithSuccess($content, new ContentTransformer);
        }
        return $this->respondNotFound();
    }

    /**
     * Stores newly created content in database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $this->authorize('create', Content::class);
        $input   = $this->validator->validate('create');
        $content = $this->repository->create($input, auth()->user());
        return $this->respondWithSuccess($content, new ContentTransformer);
    }

    /**
     * Updates the specified resource in the database.
     *
     * @param int $id Content id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $content = $this->repository->getById($id);
        if (!empty($content)) {
            $this->authorize('update', $content);
            $input   = $this->validator->validate('update');
            $content = $this->repository->update($content, $input, auth()->user());
            return $this->respondWithSuccess($content, new ContentTransformer);
        }
        return $this->respondNotFound();
    }

    /**
     * Removes the specified resource from database.
     *
     * @param Request $request Request object
     * @param int     $id      Content id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $forceDelete = $request->get('force', false);

        $content = $this->repository->getByIdWithTrashed($id);

        if (!empty($content)) {
            $this->authorize('delete', $content);
            if ($forceDelete) {
                $this->repository->forceDelete($content);
            } else {
                $this->repository->delete($content);
            }
            return $this->respondWithSimpleSuccess(['success' => true]);
        }

        return $this->respondNotFound();
    }

    /**
     * Restore soft deleted content
     *
     * @param int $id Content id
     *
     * @return mixed
     */
    public function restore($id)
    {
        $content = $this->repository->getDeletedById($id);
        if (!empty($content)) {
            $this->authorize('update', $content);
            $content->restore();
            return $this->respondWithSimpleSuccess(['success' => true]);
        }
        return $this->respondNotFound();
    }

    /**
     * Sync files with specific content
     *
     * @param int $contentId Content id
     *
     * @return mixed
     */
    public function syncFiles($contentId)
    {
        $content = $this->repository->getById($contentId);
        if (empty($content)) {
            return $this->respondNotFound();
        }
        $this->authorize('update', $content);
        $input   = $this->validator->validate('syncFiles');
        $content = $this->fileRepository->syncWith($content, $this->buildSyncData($input));
        return $this->respondWithSuccess($content);
    }

    /**
     * It builds syncData
     *
     * @param array $input Validated input
     *
     * @return mixed
     */
    protected function buildSyncData(array $input)
    {
        $syncData = [];
        foreach ($input['data'] as $item) {
            $syncData[$item['id']] = ['weight' => isset($item['weight']) ? $item['weight'] : 0];
        }
        return $syncData;
    }

}
