<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Core\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateContent {

    /** @var array */
    protected $attributes;

    /** @var User */
    protected $author;

    /**
     * Create a new job instance.
     *
     * @param array $attributes Array of attributes
     * @param User  $author     User model
     *
     */
    public function __construct(
        array $attributes = [],
        User $author = null
    ) {
        $this->author     = $author;
        $this->attributes = array_only(
            $attributes,
            [
                'type',
                'theme',
                'weight',
                'is_active',
                'is_on_home',
                'is_promoted',
                'is_sticky',
                'is_comment_allowed',
                'published_at'
            ]
        );
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $content = DB::transaction(
            function () {
                $author  = $this->author ?: Auth::user();
                $content = new Content();
                $content->fill($this->attributes);
                $content->author()->associate($author);
                $content->setAsRoot();
                $content->save();
                return $content;
            }
        );
        event('content.created', [$content]);
        return $content;
    }

}
