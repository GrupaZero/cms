<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Block;
use Illuminate\Support\Facades\DB;

class DeleteBlock {

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
        return DB::transaction(
            function () {
                // Detach all files
                $this->block->files()->sync([]);
                $lastAction = $this->block->delete();

                event('block.deleted', [$this->block]);

                return $lastAction;
            }
        );
    }

}
