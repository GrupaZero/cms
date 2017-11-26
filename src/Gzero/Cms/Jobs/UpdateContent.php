<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\File;
use Gzero\Core\Exception;
use Illuminate\Support\Facades\DB;

class UpdateContent {

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
     * @throws Exception
     *
     * @return Content
     */
    public function handle()
    {
        $content = DB::transaction(
            function () {
                $this->content->fill($this->attributes);
                $this->handleThumb();

                if ($this->parentId === null) {
                    $this->content->setAsRoot();
                }

                if ($this->parentId !== $this->content->parent_id) {

                    if ($this->content->type->name === 'category') {
                        if (!empty($this->content->children()->first())) {
                            throw new Exception('Only parent for the category without children can be updated');
                        }
                    };

                    $parent = Content::find($this->parentId);
                    if (!$parent) {
                        throw new Exception('Parent not found');
                    }

                    $this->content->setChildOf($parent);
                } else {
                    $this->content->save();
                }

                event('content.updated', [$this->content]);
                return $this->content;
            }
        );
        return $content;
    }

    /**
     * It handles thumb relation
     *
     * @throws Exception
     *
     * @return void
     */
    private function handleThumb()
    {
        if ($this->thumbId !== $this->content->thumb_id) {
            if ($this->thumbId === null) {
                $this->content->thumb()->dissociate();
            }

            $thumb = File::find($this->thumbId);
            if (empty($thumb)) {
                throw new Exception('Thumbnail file does not exist');
            }
            $this->content->thumb()->associate($thumb);
        }
    }
}
