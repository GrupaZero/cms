<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\BlockCollection;
use Gzero\Cms\Jobs\ForceDeleteBlock;
use Gzero\Cms\Jobs\RestoreBlock;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Http\Resources\Block as BlockResource;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Cms\Validators\BlockValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

/**
 * Class DeletedBlockController
 */
class DeletedBlockController extends ApiController {

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
     * @param BlockValidator      $validator  Block validator
     * @param Request             $request    Request object
     */
    public function __construct(BlockReadRepository $repository, BlockValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display list of soft deleted blocks
     *
     * @SWG\Get(
     *   path="/deleted-blocks",
     *   tags={"blocks"},
     *   summary="List of soft deleted blocks",
     *   description="List of all soft deleted blocks",
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
        $this->authorize('readList', Block::class);

        $processor
            ->addFilter(new DateRangeParser('deleted_at'))
            ->process($this->request);

        $results = $this->repository->getManyDeleted($processor->buildQueryBuilder());
        $results->setPath(apiUrl('deleted-blocks'));

        return new BlockCollection($results);
    }

    /**
     * Restore soft deleted block
     *
     * @SWG\Post(path="/deleted-blocks/{id}/restore",
     *   tags={"blocks"},
     *   summary="Restores soft deleted block",
     *   description="Restores soft deleted block.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of soft deleted block that needs to be restored.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Block"),
     *   ),
     *   @SWG\Response(response=404, description="Block not found")
     * )
     *
     * @param int $id Block id
     *
     * @return BlockResource
     */
    public function restore($id)
    {
        $block = $this->repository->getDeletedById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $block);
        dispatch_now(new RestoreBlock($block));

        return new BlockResource($this->repository->loadRelations($block));
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/deleted-blocks/{id}",
     *   tags={"blocks"},
     *   summary="Deletes soft deleted block",
     *   description="Deletes soft deleted block.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of soft deleted block that needs to be deleted.",
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
        $block = $this->repository->getDeletedById($id);

        if (!$block) {
            return $this->errorNotFound();
        }

        $this->authorize('delete', $block);
        dispatch_now(new ForceDeleteBlock($block));

        return $this->successNoContent();
    }
}
