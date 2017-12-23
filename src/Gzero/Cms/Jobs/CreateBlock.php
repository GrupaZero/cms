<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\BlockTranslation;
use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class CreateBlock {

    use DBTransactionTrait;

    /** @var string */
    protected $title;

    /** @var string */
    protected $language;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'type'          => 'basic',
        'region'        => null,
        'theme'         => null,
        'weight'        => 0,
        'filter'        => null,
        'options'       => false,
        'is_active'     => false,
        'is_cacheable'  => false,
        'body'          => null,
        'custom_fields' => null
    ];

    /**
     * Create a new job instance.
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     */
    protected function __construct(string $title, Language $language, User $author, array $attributes = [])
    {
        $this->title      = $title;
        $this->language   = $language;
        $this->author     = $author;
        $this->attributes = array_merge(
            $this->allowedAttributes,
            array_only($attributes, array_keys($this->allowedAttributes))
        );
    }

    /**
     * It creates job to create block
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @return CreateBlock
     */
    public static function make(string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($title, $language, $author, $attributes);
    }

    /**
     * It creates job to create block
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @return CreateBlock
     */
    public static function basic(string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($title, $language, $author, array_merge($attributes, ['type' => 'basic']));
    }

    /**
     * Execute the job.
     *
     * @throws \InvalidArgumentException
     * @throws \Exception|\Throwable
     *
     * @return Block
     */
    public function handle()
    {
        $block = $this->dbTransaction(function () {
            $block = new Block();
            $block->fill([
                'type'         => $this->attributes['type'],
                'region'       => $this->attributes['region'],
                'theme'        => $this->attributes['theme'],
                'weight'       => $this->attributes['weight'],
                'filter'       => $this->attributes['filter'],
                'options'      => $this->attributes['options'],
                'is_active'    => $this->attributes['is_active'],
                'is_cacheable' => $this->attributes['is_cacheable']

            ]);
            $block->author()->associate($this->author);
            $block->save();

            $translation = new BlockTranslation();
            $translation->fill([
                'title'         => $this->title,
                'language_code' => $this->language->code,
                'body'          => $this->attributes['body'],
                'custom_fields' => $this->attributes['custom_fields'],
                'is_active'     => true
            ]);
            $block->translations()->save($translation);

            event('block.created', [$block]);
            return $block;
        });
        return $block;
    }
}
