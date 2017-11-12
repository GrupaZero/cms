<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;

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
        return $this->content->delete();
    }

}
