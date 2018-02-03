<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\BlockFinder;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Cms\Http\Resources\BlockCollection;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

class ContentBlockController extends ApiController {
    /** @var ContentReadRepository */
    protected $repository;

    /** @var BlockReadRepository */
    protected $blockRepository;

    /** @var BlockFinder */
    protected $finder;

    /** @var ContentValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * ContentBlockController constructor.
     *
     * @param ContentReadRepository $repository      Content repository
     * @param BlockReadRepository   $blockRepository Block repository
     * @param BlockFinder           $finder          Block finer
     * @param ContentValidator      $validator       Content's validator
     * @param Request               $request         Request object
     */
    public function __construct(
        ContentReadRepository $repository,
        BlockReadRepository $blockRepository,
        BlockFinder $finder,
        ContentValidator $validator,
        Request $request
    ) {
        $this->repository      = $repository;
        $this->blockRepository = $blockRepository;
        $this->finder          = $finder;
        $this->validator       = $validator->setData($request->all());
        $this->request         = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="contents/{id}/blocks",
     *   tags={"content"},
     *   summary="List of blocks visible on content",
     *   description="List of blocks visible on content",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of content for which blocks need to be returned.",
     *     required=true,
     *     type="integer"
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
     *  ),
     *   @SWG\Response(response=404, description="Content or blocks not found"),
     * )
     *
     * @param int $id Block id.
     *
     * @return BlockCollection
     *
     * @throws \Gzero\InvalidArgumentException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(int $id)
    {
        $content = $this->repository->getById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('read', $content);
        $this->authorize('readList', Block::class);

        $input      = $this->validator->validate('blocks');
        $language   = language(array_get($input, 'language_code'));
        $onlyActive = array_get($input, 'only_active', false);

        $blockIds = $this->finder->getBlocksIds($content, $onlyActive);
        $results  = $this->blockRepository->getVisibleBlocks($blockIds, $language, $onlyActive);

        return new BlockCollection($results);
    }
}
