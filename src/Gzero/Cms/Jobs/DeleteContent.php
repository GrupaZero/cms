<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Illuminate\Support\Facades\DB;

class DeleteContent {

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
                // When we're using softDelete, we need to manually softDeleted descendants rows
                foreach ($this->content->findDescendants()->get() as $node) {
                    $node->delete();
                }
                // Detach all files
                $this->content->files()->sync([]);
                $this->content->delete();

                return true;
            }
        );
    }

}
