<?php namespace Gzero\Cms\Jobs;

use Carbon\Carbon;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\File;
use Gzero\Core\DBTransactionTrait;
use Gzero\InvalidArgumentException;

class UpdateContent {

    use DBTransactionTrait;

    /** @var Content */
    protected $content;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $parentId;

    /** @var array */
    protected $thumbId;

    /** @var array */
    protected $allowedAttributes = [
        'theme',
        'weight',
        'rating',
        'is_on_home',
        'is_promoted',
        'is_sticky',
        'is_comment_allowed',
        'published_at'
    ];

    /**
     * Create a new job instance.
     *
     * @param Content $content    Content model
     * @param array   $attributes Array of optional attributes
     */
    public function __construct(Content $content, array $attributes = [])
    {
        $this->content    = $content;
        $this->thumbId    = array_get($attributes, 'thumb_id', $content->thumb_id);
        $this->parentId   = array_get($attributes, 'parent_id', $content->parent_id);
        $this->attributes = array_only($attributes, $this->allowedAttributes);
    }

    /**
     * Execute the job.
     *
     * @throws InvalidArgumentException
     * @throws \Exception|\Throwable
     *
     * @return Content
     */
    public function handle()
    {
        $content = $this->dbTransaction(function () {
            if (isset($this->attributes['published_at'])) {
                $this->attributes['published_at'] = Carbon::parse($this->attributes['published_at'])->setTimezone('UTC');
            }
            $this->content->fill($this->attributes);
            $this->handleThumb();

            if ($this->parentId === null) {
                $this->content->setAsRoot();
            }

            if ($this->parentId !== $this->content->parent_id) {

                if ($this->content->type->name === 'category') {
                    if (!empty($this->content->children()->first())) {
                        throw new InvalidArgumentException('Only parent for the category without children can be updated');
                    }
                };

                $parent = Content::find($this->parentId);
                if (!$parent) {
                    throw new InvalidArgumentException('Parent not found');
                }

                $this->content->setChildOf($parent);
            } else {
                $this->content->save();
            }

            event('content.updated', [$this->content]);
            return $this->content;
        });
        return $content;
    }

    /**
     * It handles thumb relation
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    protected function handleThumb()
    {
        if ($this->thumbId !== $this->content->thumb_id) {
            if ($this->thumbId === null) {
                $this->content->thumb()->dissociate();
            }

            $thumb = File::find($this->thumbId);
            if (empty($thumb)) {
                throw new InvalidArgumentException('Thumbnail file does not exist');
            }
            $this->content->thumb()->associate($thumb);
        }
    }
}
