<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\BlockTranslation;
use Gzero\DomainException;
use Illuminate\Support\Facades\DB;

class DeleteBlockTranslation {

    /** @var BlockTranslation */
    protected $translation;

    /**
     * Create a new job instance.
     *
     * @param BlockTranslation $translation Block translation model
     */
    public function __construct(BlockTranslation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Execute the job.
     *
     * @throws DomainException
     *
     * @return bool
     */
    public function handle()
    {
        if ($this->translation->is_active) {
            throw new DomainException('Cannot delete active translation');
        }

        return DB::transaction(
            function () {
                $lastAction = $this->translation->delete();

                event('block.translation.deleted', [$this->translation]);

                return $lastAction;
            }
        );
    }
}
