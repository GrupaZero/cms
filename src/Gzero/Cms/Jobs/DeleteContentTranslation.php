<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\ContentTranslation;
use Gzero\DomainException;
use Illuminate\Support\Facades\DB;

class DeleteContentTranslation {

    /** @var ContentTranslation */
    protected $translation;

    /**
     * Create a new job instance.
     *
     * @param ContentTranslation $translation Content translation model
     */
    public function __construct(ContentTranslation $translation)
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

                event('content.translation.deleted', [$this->translation]);

                return $lastAction;
            }
        );
    }
}
