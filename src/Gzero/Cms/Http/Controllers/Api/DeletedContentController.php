<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Jobs\ForceDeleteContent;
use Gzero\Cms\Jobs\RestoreContent;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Http\Resources\Content as ContentResource;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

/**
 * Class DeletedContentController
 *
 * @SWG\Tag(
 *   name="content",
 *   description="Everything about deleted app content"
 *   )
 */
class DeletedContentController extends ApiController {

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
     * Display list of soft deleted contents
     *
     * @SWG\Get(
     *   path="/deleted-contents",
     *   tags={"content"},
     *   summary="List of soft deleted contents",
     *   description="List of all soft deleted contents",
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
            ->addFilter(new DateRangeParser('deleted_at'))
            ->process($this->request);

        $results = $this->repository->getManyDeleted($processor->buildQueryBuilder());
        $results->setPath(apiUrl('deleted-contents'));

        return new ContentCollection($results);
    }

    /**
     * Restore soft deleted content
     *
     * @SWG\Patch(path="/deleted-contents/{id}",
     *   tags={"content"},
     *   summary="Restores soft deleted content",
     *   description="Restores soft deleted content.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of soft deleted content that needs to be restored.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Content"),
     *   ),
     *   @SWG\Response(response=404, description="Content not found")
     * )
     *
     * @param int $id Content id
     *
     * @return ContentResource
     */
    public function restore($id)
    {
        $content = $this->repository->getDeletedById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        $this->authorize('update', $content);
        dispatch_now(new RestoreContent($content));

        return new ContentResource($this->repository->loadRelations($content));
    }

    /**
     * Removes the specified resource from database.
     *
     * @SWG\Delete(path="/deleted-contents/{id}",
     *   tags={"content"},
     *   summary="Deletes soft deleted content",
     *   description="Deletes soft deleted content.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of soft deleted content that needs to be deleted.",
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
        $content = $this->repository->getDeletedById($id);

        if (!$content) {
            return $this->errorNotFound();
        }

        dispatch_now(new ForceDeleteContent($content));

        return $this->successNoContent();
    }
}
