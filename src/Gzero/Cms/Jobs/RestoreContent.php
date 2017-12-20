<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Illuminate\Support\Facades\DB;

class RestoreContent {

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
                $lastAction = $this->content->restore();

                event('content.restored', [$this->content]);

                return $lastAction;
            }
        );
    }

}
