<?php namespace Gzero\Cms\Http\Controllers\Api;

use Gzero\Cms\Http\Resources\ContentCollection;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Validators\ContentValidator;
use Gzero\Core\Http\Controllers\ApiController;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Parsers\StringParser;
use Gzero\Core\UrlParamsProcessor;
use Illuminate\Http\Request;

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


    public function index(UrlParamsProcessor $processor, $id = null)
    {
        return "OK";
    }

}
