<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Models\Block;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Cms\Validators\BlockValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Http\Resources\File as FileResource;
use Gzero\Core\Http\Resources\FileCollection;
use Gzero\Core\Models\File;
use Illuminate\Http\Request;

/**
 * Class BlockFileController
 *
 * @SWG\Tag(
 *   name="block",
 *   description="Everything about app block"
 *   )
 */
class BlockFileController extends ApiController
{
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
     *   path="/blocks/{id}/files",
     *   tags={"blocks, files"},
     *   summary="List of files synced with block",
     *   description="List of files synced with block",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of block which files need to be returned.",
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
     *  )
     * )
     *
     * @param $id Block id.
     *
     * @return FileCollection
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index($id)
    {
        $block = $this->repository->getById($id);
        if (!$block) {
            return $this->errorNotFound();
        }
        $this->authorize('read', $block);

        $this->authorize('readList', File::class);
        $files = $block->files;
        if (!$files) {
            return $this->errorNotFound();
        }

        return new FileCollection($files);
    }
}
