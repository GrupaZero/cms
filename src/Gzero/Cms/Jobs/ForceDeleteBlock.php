<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Block;
use Gzero\Core\DBTransactionTrait;

class ForceDeleteBlock {

    use DBTransactionTrait;

    /** @var Block */
    protected $block;

    /**
     * Create a new job instance.
     *
     * @param Block $block Block model
     */
    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->dbTransaction(function () {
            // Detach all files
            $this->block->files()->sync([]);
            $lastAction = Block::withTrashed()->find($this->block->id)->forceDelete();

            event('block.deleted', [$this->block]);

            return $lastAction;
        });
    }

}
