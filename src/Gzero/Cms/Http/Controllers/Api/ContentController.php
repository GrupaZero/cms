<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\DeleteContent;
use Gzero\Cms\Jobs\UpdateContent;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Http\Resources\Content as ContentResource;
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
            ->addFilter(new StringParser('type'))
            ->addFilter(new NumericParser('level'))
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new StringParser('translations.language_code'))
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
     * Display a specified content.
     *
     * @SWG\Get(
     *   path="/contents/{id}",
     *   tags={"content"},
     *   summary="Returns a specific content by id",
     *   description="Returns a specific content by id",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of content that needs to be returned.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Content"),
     *  ),
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @param int $id content Id
     *
     * @return ContentResource
     */
    public function show($id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('read', $content);
        return new ContentResource($content);
    }

    /**
     * Stores newly created content in database.
     *
     * @SWG\Post(path="/contents",
     *   tags={"content"},
     *   summary="Stores newly created content",
     *   description="Stores newly created content",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to create.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"type, title, language_code"},
     *       @SWG\Property(property="type", type="string", example="content"),
     *       @SWG\Property(property="language_code", type="string", example="en"),
     *       @SWG\Property(property="title", type="string", example="Example title"),
     *       @SWG\Property(property="teaser", type="string", example="Example Teaser"),
     *       @SWG\Property(property="body", type="string", example="Example Body"),
     *       @SWG\Property(property="seo_title", type="string", example="Example SEO Title"),
     *       @SWG\Property(property="seo_description", type="string", example="Example SEO Description"),
     *       @SWG\Property(property="is_active", type="boolean", example="true"),
     *       @SWG\Property(property="parent_id", type="numeric", example="1"),
     *       @SWG\Property(property="published_at", type="string", format="date-time"),
     *       @SWG\Property(property="is_on_home", type="boolean", example="true"),
     *       @SWG\Property(property="is_promoted", type="boolean", example="true"),
     *       @SWG\Property(property="is_sticky", type="boolean", example="true"),
     *       @SWG\Property(property="is_comment_allowed", type="boolean", example="true"),
     *       @SWG\Property(property="theme", type="string", example="is-content"),
     *       @SWG\Property(property="weight", type="numeric", example="10"),
     *     )
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Content"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @return ContentResource
     */
    public function store()
    {
        $this->authorize('create', Content::class);

        $input = $this->validator->validate('create');

        $author   = auth()->user();
        $title    = array_get($input, 'title');
        $language = language(array_get($input, 'language_code'));
        $data     = array_except($input, ['title', 'language_code']);

        $content = dispatch_now(CreateContent::make($title, $language, $author, $data));

        return new ContentResource($this->repository->loadRelations($content));
    }

    /**
     * Updates the specified resource in the database.
     *
     * @param int $id Content id
     *
     * @SWG\Patch(path="/contents/{id}",
     *   tags={"content"},
     *   summary="Updates specified content",
     *   description="Updates specified content",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of content that needs to be updated.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to update.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="parent_id", type="numeric", example="1"),
     *       @SWG\Property(property="thumb_id", type="numeric", example="1"),
     *       @SWG\Property(property="published_at", type="string", format="date-time"),
     *       @SWG\Property(property="is_on_home", type="boolean", example="true"),
     *       @SWG\Property(property="is_promoted", type="boolean", example="true"),
     *       @SWG\Property(property="is_sticky", type="boolean", example="true"),
     *       @SWG\Property(property="is_comment_allowed", type="boolean", example="true"),
     *       @SWG\Property(property="theme", type="string", example="is-content"),
     *       @SWG\Property(property="weight", type="numeric", example="10"),
     *       @SWG\Property(property="rating", type="numeric", example="10"),
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Content"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @return ContentResource
     */
    public function update($id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $content);

        $input = $this->validator->validate('update');

        $content = dispatch_now(new UpdateContent($content, $input));

        return new ContentResource($this->repository->loadRelations($content));
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/contents/{id}",
     *   tags={"content"},
     *   summary="Soft deletes specified content",
     *   description="Content will be mark as deleted.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of content that needs to be soft deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @param int $id Content id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('delete', $content);
        dispatch_now(new DeleteContent($content));

        return $this->successNoContent();
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
