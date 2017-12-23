<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\BlockTranslation;
use Gzero\Core\DBTransactionTrait;
use Gzero\DomainException;

class DeleteBlockTranslation {

    use DBTransactionTrait;

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

        return $this->dbTransaction(function () {
            $lastAction = $this->translation->delete();

            event('block.translation.deleted', [$this->translation]);

            return $lastAction;
        });
    }
}
