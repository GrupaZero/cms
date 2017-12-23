<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\Route;

class ForceDeleteContent {

    use DBTransactionTrait;

    /** @var Content */
    protected $content;

    /**
     * Create a new job instance.
     *
     * @param Content $content Content model
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->dbTransaction(function () {
            $descendantsIds = $this->content->findDescendantsWithTrashed()->pluck('id');

            // First we need to delete all routes because it's polymorphic relation
            Route::where('routes.routable_type', '=', Content::class)
                ->whereIn('routable_id', $descendantsIds)
                ->delete();
            $lastAction = Content::withTrashed()->find($this->content->id)->forceDelete();

            event('content.force_deleted', [$this->content]);

            return $lastAction;
        });
    }
}
