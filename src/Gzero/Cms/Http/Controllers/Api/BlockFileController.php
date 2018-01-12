<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Http\Resources\FileCollection;
use Gzero\Core\Jobs\SyncFiles;
use Gzero\Core\Models\File;
use Gzero\Core\Validators\FileValidator;
use Illuminate\Http\Request;

/**
 * Class BlockFileController
 *
 * @SWG\Tag(
 *   name="blocks",
 *   description="Everything about app blocks"
 *   )
 */
class BlockFileController extends ApiController
{
    /** @var BlockReadRepository */
    protected $repository;

    /** @var FileValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * BlockController constructor.
     *
     * @param BlockReadRepository $repository Block repository
     * @param FileValidator       $validator  Content validator
     * @param Request             $request    Request object
     */
    public function __construct(BlockReadRepository $repository, FileValidator $validator, Request $request)
    {
        $this->validator  = $validator->setData($request->all());
        $this->repository = $repository;
        $this->request    = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="blocks/{id}/files",
     *   tags={"blocks"},
     *   summary="List of files synced with block",
     *   description="List of files synced with block",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of block for which files need to be returned.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/File")),
     *  ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="Block or files not found"),
     * )
     *
     * @param int $id Block id.
     *
     * @return FileCollection
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(int $id)
    {
        $block = $this->repository->getById($id);
        if (!$block) {
            return $this->errorNotFound();
        }
        $this->authorize('read', $block);

        $this->authorize('readList', File::class);
        $files = $block->files->load('translations');
        if (!$files) {
            return $this->errorNotFound();
        }

        return new FileCollection($files);
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Post(
     *   path="blocks/{id}/files",
     *   tags={"blocks"},
     *   summary="Sync files for specified block",
     *   description="Sync files for specified block",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of block for which files need to be updated.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Files ids to sync.",
     *     required=true,
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         title="id",
     *         description="Id of existing file.",
     *         type="numeric",
     *         example="1",
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/File"),
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Validation Error",
     *     @SWG\Schema(ref="#/definitions/ValidationErrors")
     *  ),
     *   @SWG\Response(response=404, description="Block not found")
     * )
     *
     * @param int $id Block id
     *
     * @return FileCollection
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store($id)
    {
        $block = $this->repository->getById($id);
        if (!$block) {
            return $this->errorNotFound();
        }
        $this->authorize('update', $block);

        dispatch_now(new SyncFiles($block, $this->request->all()));

        $files = $block->fresh()->files->load('translations');

        return new FileCollection($files);
    }
}
