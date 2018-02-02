<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;

/**
 * Class NestedContentController
 *
 */
class ContentTreeController extends ApiController {

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
     * @param UrlParamsProcessor $processor
     *
     * @return string
     */
    public function index(UrlParamsProcessor $processor)
    {
        // @TODO Implement contents/{id}/tree
        $processor
            ->addFilter(new BoolParser('only_categories'))
            ->process($this->request);

        Resource::wrap('data');
        return (new ContentCollection($this->repository->getTree($processor->buildQueryBuilder())));
    }

}
