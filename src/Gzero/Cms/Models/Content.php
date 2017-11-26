<?php namespace Gzero\Cms\Models;

use Gzero\Cms\Handlers\Content\ContentTypeHandler;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Gzero\Cms\Presenters\ContentPresenter;
use Gzero\Core\Exception;
use Gzero\EloquentTree\Model\Tree;
use Illuminate\Http\Response;
use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Tree implements PresentableInterface, Uploadable, Routable {

    use SoftDeletes;

    /** @var array */
    protected $fillable = [
        'type',
        'theme',
        'path',
        'weight',
        'rating',
        'is_on_home',
        'is_comment_allowed',
        'is_promoted',
        'is_sticky',
        'published_at'
    ];

    /** @var array */
    protected $attributes = [
        'is_on_home'         => false,
        'is_comment_allowed' => false,
        'is_promoted'        => false,
        'is_sticky'          => false
    ];

    /**
     * @var array
     */
    protected $dates = ['published_at', 'deleted_at'];

    /**
     * Content type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Polymorphic relation with route
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphMany
     */
    public function routes()
    {
        return $this->morphMany(Route::class, 'routable');
    }

    /**
     * Translation one to many relation
     *
     * @param bool $active Only active translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations($active = true)
    {
        if ($active) {
            return $this->hasMany(ContentTranslation::class)->where('is_active', '=', 1);
        }
        return $this->hasMany(ContentTranslation::class);
    }

    /**
     * Get all of the files for the content.
     *
     * @param bool $active Only active file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files($active = true)
    {
        if ($active) {
            return $this->morphToMany(File::class, 'uploadable')->where('is_active', '=', 1)->withPivot('weight')
                ->withTimestamps();
        }
        return $this->morphToMany(File::class, 'uploadable')->withPivot('weight')->withTimestamps();
    }

    /**
     * Content author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Content thumb relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function thumb()
    {
        return $this->belongsTo(File::class, 'thumb_id', 'id');
    }

    /**
     * Get Content url in specified language.
     *
     * @param string $languageCode Lang code
     *
     * @throws Exception
     *
     * @return string
     */
    public function getPath($languageCode)
    {
        $routeTranslation = $this->routes()->newQuery()
            ->where('language_code', $languageCode)
            ->first();
        if (empty($routeTranslation->path)) {
            throw new Exception("There is no route in '$languageCode' language for Content id: $this->id");
        }
        return $routeTranslation->path;
    }

    /**
     * Returns active translation in specific language
     *
     * @param string $languageCode Language code
     *
     * @return mixed
     */
    public function getActiveTranslation($languageCode)
    {
        return $this->translations->first(function ($translation) use ($languageCode) {
            return $translation->is_active === true && $translation->language_code === $languageCode;
        });
    }

    /**
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new ContentPresenter($this);
    }

    /**
     * Return true if content can be shown on frontend
     *
     * @return bool
     */
    public function canBeShown()
    {
        return $this->is_active;
    }

    /**
     * Find all trashed descendants for specific node with this node as root
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findDescendantsWithTrashed()
    {
        return static::withTrashed()->where($this->getTreeColumn('path'), 'LIKE', $this->{$this->getTreeColumn('path')} . '%')
            ->orderBy($this->getTreeColumn('level'), 'ASC');
    }

    /**
     * Handle content rendering by type
     *
     * @param Language $language Language
     *
     * @return Response
     */
    public function handle(Language $language): Response
    {
        // We need to unify response with repository format
        $this->load(ContentReadRepository::$loadRelations);
        return $this->getHandler()->handle($this, $language);
    }

    /**
     * Creates route with unique path based on content translation title, and tree hierarchy
     *
     * @param ContentTranslation $translation Translation
     * @param bool               $isActive    Is active trigger
     *
     * @return $this
     */
    public function createRoute(ContentTranslation $translation, bool $isActive = false)
    {
        $route = Route::firstOrNew([
            'language_code' => $translation->language_code,
            'path'          => $this->getSlug($translation),
        ], [
            'is_active' => $isActive
        ]);

        $route->path = Route::buildUniquePath($route->path, $translation->language_code);

        $this->routes()->save($route);

        return $this;
    }

    /**
     * Function sets all content translations in provided language code as inactive
     *
     * @param string $languageCode language code
     *
     * @return mixed
     */
    public function disableActiveTranslations($languageCode)
    {
        return $this->translations()
            ->where('content_id', $this->id)
            ->where('language_code', $languageCode)
            ->update(['is_active' => false]);
    }

    /**
     * @param string $type Content type
     *
     * @throws Exception
     *
     * @return void
     */
    public function setTypeAttribute($type)
    {
        if (!$type instanceof ContentType) {
            $type = ContentType::getByName($type);
        }
        if (!$type) {
            throw new Exception('Unknown content type');
        }
        $this->type()->associate($type);
    }

    /**
     * Returns content slug from translation title with parent slug
     *
     * @param ContentTranslation $translation Content translation
     *
     * @return string an unique route path address in specified language
     */
    protected function getSlug(ContentTranslation $translation)
    {
        $path = str_slug($translation->title);

        if ($this->parent_id) {
            $path = $this->parent->getPath($translation->language_code) . '/' . $path;
        }

        return $path;
    }

    /**
     * Dynamically resolve content handler
     *
     * @return ContentTypeHandler
     * @throws Exception
     */
    protected function getHandler()
    {
        $handler = resolve($this->type->handler);
        if (!$handler instanceof ContentTypeHandler) {
            throw new Exception("Type: $this->type must implement ContentTypeInterface");
        }
        return $handler;
    }

    /**
     * @param Tree|Content $parent Parent category
     *
     * @return Content|Tree
     * @throws Exception
     */
    public function setChildOf(Tree $parent)
    {
        if ($parent->type->name !== 'category') {
            throw new Exception("Content can be assigned only to category parent");
        }
        return parent::setChildOf($parent);
    }

    /**
     * Check if entity exists
     *
     * @param int $id entity id
     *
     * @return boolean
     */
    public static function checkIfExists($id)
    {
        return self::where('id', $id)->exists();
    }
}
