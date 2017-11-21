<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Repositories\RepositoryValidationException;
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
     * @return bool
     *
     * @throws RepositoryValidationException
     */
    public function handle()
    {
        if ($this->translation->is_active) {
            throw new RepositoryValidationException('Cannot delete active translation');
        }

        return DB::transaction(
            function () {
                return $this->translation->delete();
            }
        );
    }

}
