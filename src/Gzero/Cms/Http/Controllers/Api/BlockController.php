<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Cms\Jobs\DeleteBlock;
use Gzero\Cms\Jobs\UpdateBlock;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Validators\BlockValidator;
use Gzero\Cms\Http\Resources\BlockCollection;
use Gzero\Cms\Http\Resources\Block as BlockResource;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

/**
 * Class BlockController
 *
 * @SWG\Tag(
 *   name="blocks",
 *   description="Everything about app blocks"
 *   )
 */
class BlockController extends ApiController {

    /** @var BlockReadRepository */
    protected $repository;

    /** @var BlockValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * BlockController constructor.
     *
     * @param BlockReadRepository $repository Block repository
     * @param BlockValidator      $validator  Content validator
     * @param Request             $request    Request object
     */
    public function __construct(BlockReadRepository $repository, BlockValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/blocks",
     *   tags={"blocks"},
     *   summary="List of all blocks",
     *   description="List of all available blocks",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Type to filter by",
     *     required=false,
     *     type="string",
     *     default="basic"
     *   ),
     *   @SWG\Parameter(
     *     name="region",
     *     in="query",
     *     description="Region to filter by",
     *     required=false,
     *     type="string",
     *     default="sidebarLeft"
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
     *     name="is_active",
     *     in="query",
     *     description="Active blocks filter",
     *     required=false,
     *     type="boolean",
     *     default="true"
     *   ),
     *   @SWG\Parameter(
     *     name="is_cacheable",
     *     in="query",
     *     description="Cacheable blocks filter",
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
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Block")),
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
     * @return BlockCollection
     */
    public function index(UrlParamsProcessor $processor)
    {
        $this->authorize('readList', Content::class);

        $processor
            ->addFilter(new StringParser('type'))
            ->addFilter(new StringParser('region'))
            ->addFilter(new NumericParser('author_id'))
            ->addFilter(new BoolParser('is_active'))
            ->addFilter(new BoolParser('is_cacheable'))
            ->addFilter(new DateRangeParser('created_at'))
            ->addFilter(new DateRangeParser('updated_at'))
            ->process($this->request);

        $results = $this->repository->getMany($processor->buildQueryBuilder());
        $results->setPath(apiUrl('blocks'));

        return new BlockCollection($results);
    }

    /**
     * Display a specified block.
     *
     * @SWG\Get(
     *   path="/blocks/{id}",
     *   tags={"blocks"},
     *   summary="Returns a specific block by id",
     *   description="Returns a specific block by id",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of block that needs to be returned.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Block"),
     *  ),
     *   @SWG\Response(response=404, description="Block not found")
     * )
     *
     * @param int $id content Id
     *
     * @return BlockResource
     */
    public function show($id)
    {
        $block = $this->repository->getById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $this->authorize('read', $block);
        return new BlockResource($block);
    }

    /**
     * Stores newly created content in database.
     *
     * @SWG\Post(path="/blocks",
     *   tags={"blocks"},
     *   summary="Stores newly created block",
     *   description="Stores newly created block",
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
     *       @SWG\Property(property="type", type="string", example="basic"),
     *       @SWG\Property(property="title", type="string", example="Example title"),
     *       @SWG\Property(property="language_code", type="string", example="en"),
     *       @SWG\Property(property="region", type="string", example="sidebarLeft"),
     *       @SWG\Property(property="theme", type="string", example="is-content"),
     *       @SWG\Property(property="weight", type="numeric", example="10"),
     *       @SWG\Property(property="filter", type="json", example="{'key':'value'}"),
     *       @SWG\Property(property="options", type="json", example="{'key':'value'}"),
     *       @SWG\Property(property="is_active", type="boolean", example="true"),
     *       @SWG\Property(property="is_cacheable", type="boolean", example="true"),
     *       @SWG\Property(property="body", type="string", example="Example Body"),
     *       @SWG\Property(property="custom_fields", type="json", example="{'key':'value'}"),
     *     )
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Block"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  )
     * )
     *
     * @return BlockResource
     */
    public function store()
    {
        $this->authorize('create', Block::class);

        $input = $this->validator->validate('create');

        $author   = auth()->user();
        $title    = array_get($input, 'title');
        $language = language(array_get($input, 'language_code'));
        $data     = array_except($input, ['title', 'language_code']);

        $block = dispatch_now(CreateBlock::make($title, $language, $author, $data));

        return new BlockResource($this->repository->loadRelations($block));
    }

    /**
     * Updates the specified resource in the database.
     *
     * @param int $id Block id
     *
     * @SWG\Patch(path="/blocks/{id}",
     *   tags={"blocks"},
     *   summary="Updates specified block",
     *   description="Updates specified block",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of block that needs to be updated.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Block"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="Block not found")
     * )
     *
     * @return BlockResource
     */
    public function update($id)
    {
        $block = $this->repository->getById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $block);

        $input = $this->validator->validate('update');

        $block = dispatch_now(new UpdateBlock($block, $input));

        return new BlockResource($this->repository->loadRelations($block));
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/blocks/{id}",
     *   tags={"blocks"},
     *   summary="Deletes specified block",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of block that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Successful operation"
     *   ),
     *   @SWG\Response(response=404, description="Block not found")
     * )
     *
     * @param int $id Block id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $block = $this->repository->getById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        dispatch_now(new DeleteBlock($block));

        return $this->successNoContent();
    }
}
