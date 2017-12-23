<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Core\DBTransactionTrait;

class DeleteContent {

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
            // When we're using softDelete, we need to manually softDeleted descendants rows
            foreach ($this->content->findDescendants()->get() as $node) {
                $node->delete();
            }
            // Detach all files
            $this->content->files()->sync([]);
            $lastAction = $this->content->delete();

            event('content.deleted', [$this->content]);

            return $lastAction;
        });
    }

}
