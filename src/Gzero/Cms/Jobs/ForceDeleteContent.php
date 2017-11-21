<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
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
                DB::table($routeRelation->getModel()->getTable())
                    ->where($routeRelation->getMorphType(), '=', get_class($this->content))
                    ->whereIn($routeRelation->getForeignKeyName(), $descendantsIds)
                    ->delete();
                Content::withTrashed()->find($this->content->id)->forceDelete();

                return true;
            }
        );
    }
}
