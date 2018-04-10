<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Core\DBTransactionTrait;

class RestoreContent {

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
            $lastAction = $this->content->restore();

            event('content.restored', [$this->content]);

            return $lastAction;
        });
    }

}
