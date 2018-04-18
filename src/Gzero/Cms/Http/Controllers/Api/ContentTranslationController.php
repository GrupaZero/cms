<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentTranslationCollection;
use Gzero\Cms\Http\Resources\ContentTranslation as ContentTranslationResource;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Jobs\DeleteContentTranslation;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentTranslationValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\DateTimeRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

class ContentTranslationController extends ApiController {

    /** @var ContentReadRepository */
    protected $repository;

    /** @var ContentTranslationValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * ContentTranslationController constructor
     *
     * @param ContentReadRepository       $repository Content repository
     * @param ContentTranslationValidator $validator  Content validator
     * @param Request                     $request    Request object
     */
    public function __construct(ContentReadRepository $repository, ContentTranslationValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/contents/{id}/translations",
     *   tags={"content"},
     *   summary="List of all content translations",
     *   description="List of all available content translations",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="author_id",
     *     in="query",
     *     description="Author id to filter by",
     *     required=false,
     *     type="integer",
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     name="is_active",
     *     in="query",
     *     description="Active translation filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
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
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/ContentTranslation")),
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
     * @return ContentTranslationCollection
     */
    public function index(UrlParamsProcessor $processor, $id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('readList', Content::class);

        $processor
            ->addFilter(new StringParser('language_code'), 'in:pl,en,de,fr')
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('is_active'))
            ->addFilter(new DateTimeRangeParser('created_at'))
            ->addFilter(new DateTimeRangeParser('updated_at'))
            ->process($this->request);

        $results = $this->repository->getManyTranslations($content, $processor->buildQueryBuilder());
        $results->setPath(apiUrl('contents/{id}/translations', ['id' => $id]));

        return new ContentTranslationCollection($results);
    }

    /**
     * Stores newly created translation for specified content entity in database.
     *
     * @SWG\Post(path="/contents/{id}/translations",
     *   tags={"content"},
     *   summary="Stores newly created content translation",
     *   description="Stores newly created content translation",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to create.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"title, language_code"},
     *       @SWG\Property(property="language_code", type="string", example="en"),
     *       @SWG\Property(property="title", type="string", example="Example title"),
     *       @SWG\Property(property="teaser", type="string", example="Example Teaser"),
     *       @SWG\Property(property="body", type="string", example="Example Body"),
     *       @SWG\Property(property="seo_title", type="string", example="Example SEO Title"),
     *       @SWG\Property(property="seo_description", type="string", example="Example SEO Description"),
     *       @SWG\Property(property="is_active", type="boolean", example="true"),
     *     )
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/ContentTranslation"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @param int $id Id of the content
     *
     * @return ContentTranslationResource
     */
    public function store($id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('create', $content);

        $input = $this->validator->validate('create');

        $author   = auth()->user();
        $title    = array_get($input, 'title');
        $language = language(array_get($input, 'language_code'));
        $data     = array_except($input, ['title', 'language_code']);

        $translation = dispatch_now(new AddContentTranslation($content, $title, $language, $author, $data));

        return new ContentTranslationResource($translation);
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/contents/{id}/translations/{id}",
     *   tags={"content"},
     *   summary="Deletes specified content translation",
     *   description="Deletes specified content translation.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of content that holds translation.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="translationId",
     *     in="path",
     *     description="Id of content translation that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=404, description="Content or content translation not found"),
     *   @SWG\Response(response=400, description="Cannot delete active translation")
     * )
     *
     * @param int $id            Content id
     * @param int $translationId Translation id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $translationId)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('delete', $content);
        $translation = $content->translations(false)->find($translationId);

        if (!$translation) {
            return $this->errorNotFound();
        }

        dispatch_now(new DeleteContentTranslation($translation));

        return $this->successNoContent();
    }
}
