<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\BlockTranslation;
use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class AddBlockTranslation {

    use DBTransactionTrait;

    /** @var Block */
    protected $block;

    /** @var string */
    protected $language;

    /** @var string */
    protected $title;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'body'          => null,
        'custom_fields' => null,
        'is_active'     => true
    ];

    /**
     * Create a new job instance.
     *
     * @param Block    $block      Block model
     * @param string   $title      Title
     * @param Language $language   Language code
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @internal param array $attributes Array of attributes
     */
    public function __construct(Block $block, string $title, Language $language, User $author, array $attributes = [])
    {
        $this->block      = $block;
        $this->language   = $language;
        $this->title      = $title;
        $this->author     = $author;
        $this->attributes = array_merge(
            $this->allowedAttributes,
            array_only($attributes, array_keys($this->allowedAttributes))
        );
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $translation = $this->dbTransaction(function () {
            $translation = new BlockTranslation();
            $translation->fill([
                'title'         => $this->title,
                'language_code' => $this->language->code,
                'body'          => $this->attributes['body'],
                'custom_fields' => $this->attributes['custom_fields'],
                'is_active'     => true,
            ]);
            $translation->author()->associate($this->author);

            $this->block->disableActiveTranslations($translation->language_code);
            $this->block->translations()->save($translation);

            event('block.translation.created', [$translation]);
            return $translation;
        });
        return $translation;
    }
}
