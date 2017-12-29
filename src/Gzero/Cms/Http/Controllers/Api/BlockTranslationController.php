<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\BlockTranslationCollection;
use Gzero\Cms\Jobs\AddBlockTranslation;
use Gzero\Cms\Jobs\DeleteBlockTranslation;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Cms\Validators\BlockTranslationValidator;
use Gzero\Cms\Http\Resources\BlockTranslation as BlockTranslationResource;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

class BlockTranslationController extends ApiController {

    /** @var BlockReadRepository */
    protected $repository;

    /** @var BlockTranslationValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * ContentTranslationController constructor
     *
     * @param BlockReadRepository       $repository Block repository
     * @param BlockTranslationValidator $validator  Block validator
     * @param Request                   $request    Request object
     */
    public function __construct(BlockReadRepository $repository, BlockTranslationValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/blocks/{id}/translations",
     *   tags={"blocks"},
     *   summary="List of all block translations",
     *   description="List of all available block translations",
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
     * @return BlockTranslationCollection
     */
    public function index(UrlParamsProcessor $processor, $id)
    {
        $block = $this->repository->getById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $this->authorize('readList', Content::class);

        $processor
            ->addFilter(new StringParser('language_code'), 'in:pl,en,de,fr')
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('is_active'))
            ->addFilter(new DateRangeParser('created_at'))
            ->addFilter(new DateRangeParser('updated_at'))
            ->process($this->request);

        $results = $this->repository->getManyTranslations($block, $processor->buildQueryBuilder());
        $results->setPath(apiUrl('blocks/{id}/translations', ['id' => $id]));

        return new BlockTranslationCollection($results);
    }


    /**
     * Stores newly created translation for specified block entity in database.
     *
     * @SWG\Post(path="/blocks/{id}/translations",
     *   tags={"blocks"},
     *   summary="Stores newly created block translation",
     *   description="Stores newly created block translation",
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
     *       @SWG\Property(property="body", type="string", example="Example Body"),
     *       @SWG\Property(property="custom_fields", type="json", example="{'key':'value'}"),
     *       @SWG\Property(property="is_active", type="boolean", example="true"),
     *     )
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/BlockTranslation"),
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
     * @return BlockTranslationResource
     */
    public function store($id)
    {
        $block = $this->repository->getById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $this->authorize('create', $block);
        $this->authorize('update', $block);

        $input = $this->validator->validate('create');

        $author   = auth()->user();
        $title    = array_get($input, 'title');
        $language = language(array_get($input, 'language_code'));
        $data     = array_except($input, ['title', 'language_code']);

        $translation = dispatch_now(new AddBlockTranslation($block, $title, $language, $author, $data));

        return new BlockTranslationResource($translation);
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/blocks/{id}/translations/{id}",
     *   tags={"blocks"},
     *   summary="Deletes specified block translation",
     *   description="Deletes specified block translation.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of block that holds translation.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="translationId",
     *     in="path",
     *     description="Id of block translation that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=404, description="Block or block translation not found"),
     *   @SWG\Response(response=400, description="Cannot delete active translation")
     * )
     *
     * @param int $id            Block id
     * @param int $translationId Translation id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $translationId)
    {
        $block = $this->repository->getById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $translation = $block->translations(false)->find($translationId);

        if (!$translation) {
            return $this->errorNotFound();
        }

        dispatch_now(new DeleteBlockTranslation($translation));

        return $this->successNoContent();
    }
}
