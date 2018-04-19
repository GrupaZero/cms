<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\DeleteContent;
use Gzero\Cms\Jobs\UpdateContent;
use Gzero\Cms\Jobs\UpdateContentRoute;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Http\Resources\Content as ContentResource;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\DateTimeRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\RangeParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

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
     *     name="translations[language_code]",
     *     in="query",
     *     description="Translation relation language code to filter by",
     *     required=false,
     *     type="string",
     *     default="en"
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
     *     name="rating",
     *     in="query",
     *     description="Rating range to filter by",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     default={1,5},
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="published_at",
     *     in="query",
     *     description="DateTime range to filter by.\
                        Format: `<left-date-time>,<right-date-time>`\
                        DateTime must be in the ISO8601 format, e.g. `2019-08-12T03:32:41+09:30`",
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="created_at",
     *     in="query",
     *     description="DateTime range to filter by.\
                        Format: `<left-date-time>,<right-date-time>`\
                        DateTime must be in the ISO8601 format, e.g. `2019-08-12T03:32:41+09:30`"
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
     *     @SWG\Items(type="string")
     *   ),
     *   @SWG\Parameter(
     *     name="updated_at",
     *     in="query",
     *     description="DateTime range to filter by.\
                        Format: `<left-date-time>,<right-date-time>`\
                        DateTime must be in the ISO8601 format, e.g. `2019-08-12T03:32:41+09:30`"
     *     required=false,
     *     type="array",
     *     minItems=2,
     *     maxItems=2,
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
            ->addFilter(new BoolParser('only_published'))
            ->addFilter(new BoolParser('only_not_published'))
            ->addFilter(new BoolParser('is_sticky'))
            ->addFilter(new BoolParser('is_on_home'))
            ->addFilter(new BoolParser('is_promoted'))
            ->addFilter(new BoolParser('is_comment_allowed'))
            ->addFilter(new RangeParser('rating'))
            ->addFilter(new DateTimeRangeParser('published_at'))
            ->addFilter(new DateTimeRangeParser('created_at'))
            ->addFilter(new DateTimeRangeParser('updated_at'))
            ->process($this->request);

        if ($this->request->has('only_published')) {
            $results = $this->repository->getManyPublished($processor->buildQueryBuilder());
        } elseif ($this->request->has('only_not_published')) {
            $results = $this->repository->getManyNotPublished($processor->buildQueryBuilder());
        } else {
            $results = $this->repository->getMany($processor->buildQueryBuilder());
        }

        $results->setPath(apiUrl('contents'));

        return new ContentCollection($results);
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
     * Updates the specified resource in the database.
     *
     * @param int $id Content id
     *
     * @SWG\Patch(path="/contents/{id}/route",
     *   tags={"content"},
     *   summary="Updates route of specified content",
     *   description="Updates route of specified content",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of content which route needs to be updated.",
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
     *       @SWG\Property(property="path", type="string", example="url-slug"),
     *       @SWG\Property(property="is_active", type="boolean", example="true"),
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
    public function updateRoute($id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('updateRoute', $content);

        $input = $this->validator->validate('updateRoute');
        $language = language(array_get($input, 'language_code'));

        $content = dispatch_now(new UpdateContentRoute($content, $language, $input));

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
}
