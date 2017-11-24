<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentTranslationValidator;
use Gzero\Core\Http\Controllers\ApiController;
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
     * @param UrlParamsProcessor $processor Params processor
     * @param int|null           $id        Id used for nested resources
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(UrlParamsProcessor $processor, $id)
    {
        $this->authorize('readList', Content::class);

        $processor
            ->addFilter(new StringParser('lang'))
            ->addFilter(new StringParser('type'))
            ->addFilter(new StringParser('parent_id'))
            ->addFilter(new StringParser('is_active'))
            ->addFilter(new StringParser('level'))
            ->addFilter(new StringParser('level'))
            ->addFilter(new StringParser('trashed'))
            ->process($this->request);

        $content = $this->repository->getById($id);
        if (!empty($content)) {

            $results = $this->repository->getManyTranslations($processor->buildQueryBuilder());

            return $this->respondWithSuccess($results, new ContentTranslationTransformer);
        } else {
            return $this->respondNotFound();
        }
    }

    /**
     * Display a specified resource.
     *
     * @param int $id            Id of the content
     * @param int $translationId Id of the content translation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, $translationId)
    {
        $content = $this->getContent($id);
        if (!empty($content)) {
            $this->authorize('read', $content);
            $translation = $this->repository->getContentTranslationById($content, $translationId);
            if (!empty($translation)) {
                return $this->respondWithSuccess($translation, new ContentTranslationTransformer);
            }
        }
        return $this->respondNotFound();
    }

    /**
     * Stores newly created translation for specified content entity in database.
     *
     * @param int $id Id of the content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($id)
    {
        $content = $this->getContent($id);
        if (!empty($content)) {
            $this->authorize('create', $content);
            $this->authorize('update', $content);
            $input       = $this->validator->validate('create');
            $translation = $this->repository->createTranslation($content, $input);
            return $this->respondWithSuccess($translation, new ContentTranslationTransformer);
        }
        return $this->respondNotFound();
    }

    /**
     * Each translations update always creates new record in database, for history revision
     *
     * @param int $id Id of the content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        return $this->store($id);
    }

    /**
     * Remove the specified resource from database.
     *
     * @param int $id            Id of the content
     * @param int $translationId Id of the content translation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $translationId)
    {
        $content = $this->getContent($id);
        if (!empty($content)) {
            $this->authorize('delete', $content);
            $translation = $this->repository->getContentTranslationById($content, $translationId);
            if (!empty($translation)) {
                $this->repository->deleteTranslation($translation);
                return $this->respondWithSimpleSuccess(['success' => true]);
            }
        }
        return $this->respondNotFound();
    }

    /**
     * Gets Content entity by id
     *
     * @param int $id content id
     *
     * @return Content
     */
    protected function getContent($id)
    {
        return $this->repository->getById($id);
    }
}
