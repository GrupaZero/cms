<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Http\Resources\FileCollection;
use Gzero\Core\Jobs\SyncFiles;
use Gzero\Core\Models\File;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

class ContentFileController extends ApiController
{
    /** @var ContentReadRepository */
    protected $repository;

    /** @var ContentValidator */
    protected $validator;

    /** @var Request */
    protected $request;

    /**
     * ContentFileController constructor.
     *
     * @param ContentReadRepository $repository Content repository
     * @param ContentValidator      $validator  Content's files validator
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
     *   path="contents/{id}/files",
     *   tags={"content"},
     *   summary="List of files synced with content",
     *   description="List of files synced with content",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of content for which files need to be returned.",
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
     *   @SWG\Response(response=404, description="Content or files not found"),
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     *
     * @param int                $id        Block id.
     *
     * @return FileCollection
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(UrlParamsProcessor $processor, int $id)
    {
        $content = $this->repository->getById($id);
        if (!$content) {
            return $this->errorNotFound();
        }
        $this->authorize('read', $content);

        $this->authorize('readList', File::class);
        $processor->process($this->request);

        $results = $this->repository->getManyFiles($content, $processor->buildQueryBuilder());
        $results->setPath(apiUrl("contents/$content->id/files"));

        return new FileCollection($results);
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Put(
     *   path="contents/{id}/files",
     *   tags={"content"},
     *   summary="Sync files for specified content",
     *   description="Sync files for specified content",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of content for which files need to be updated.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Files ids to sync.",
     *     required=true,
     *     @SWG\Schema(
     *       required={"data"},
     *       @SWG\Property(property="data", type="array",
     *         @SWG\Items(
     *           type="object",
     *           @SWG\Property(property="id", description="Id of existing file that needs to be synced.", type="integer"),
     *           @SWG\Property(property="weight", description="Weight of file.", type="integer"),
     *         ),
     *       ),
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
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @param UrlParamsProcessor $processor Params processor
     *
     * @param int                $id        Content id
     *
     * @return FileCollection
     *
     * @throws \Gzero\InvalidArgumentException
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sync(UrlParamsProcessor $processor, int $id)
    {
        $content = $this->repository->getById($id);
        if (!$content) {
            return $this->errorNotFound();
        }
        $this->authorize('update', $content);

        $input = $this->validator->validate('syncFiles');

        $fileIdsWithWeight = collect(array_get($input, 'data'))->mapWithKeys(function ($item) {
            if (array_key_exists('weight', $item)) {
                return [
                    $item['id'] => [
                        'weight' => $item['weight']
                    ]
                ];
            }

            return [
                $item['id']
            ];
        })->toArray();

        dispatch_now(new SyncFiles($content, $fileIdsWithWeight));

        $results = $this->repository->getManyFiles($content, $processor->buildQueryBuilder());
        $results->setPath(apiUrl("contents/$content->id/files"));

        return new FileCollection($results);
    }
}
