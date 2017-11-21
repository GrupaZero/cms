<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Core\Models\Route;
use Illuminate\Support\Facades\DB;

class ForceDeleteContent {

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
        return DB::transaction(
            function () {
                $routeRelation  = $this->content->route();
                $descendantsIds = $this->content->findDescendantsWithTrashed()->pluck('id');

                // First we need to delete all routes because it's polymorphic relation
                Route::query()
                    ->where('routes.routable_type', '=', Content::class)
                    ->whereIn($routeRelation->getForeignKeyName(), $descendantsIds)
                    ->delete();
                Content::withTrashed()->find($this->content->id)->forceDelete();

                return true;
            }
        );
    }
}
