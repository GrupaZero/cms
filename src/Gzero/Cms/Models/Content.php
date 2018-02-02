<?php namespace Gzero\Cms\Models;

use Carbon\Carbon;
use Gzero\DomainException;
use Gzero\InvalidArgumentException;
use Gzero\Cms\Handlers\Content\ContentTypeHandler;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Gzero\Core\Models\File;
use Gzero\Core\Models\Uploadable;
use Gzero\Cms\ViewModels\ContentViewModel;
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
            return $this->hasMany(ContentTranslation::class)->where('is_active', '=', true);
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
            return $this->morphToMany(File::class, 'uploadable')
                ->where('is_active', '=', 1)
                ->withPivot('weight')
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
     * @throws DomainException
     *
     * @return string
     */
    public function getPath($languageCode)
    {
        $routeTranslation = $this->routes()->newQuery()
            ->where('language_code', $languageCode)
            ->first();
        if (empty($routeTranslation->path)) {
            throw new DomainException("There is no route in '$languageCode' language for content: $this->id");
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
     * @return ContentViewModel
     */
    public function getPresenter()
    {
        return new ContentViewModel($this->toArray());
    }

    /**
     * Return true if content can be shown on frontend
     *
     * @TODO What about active url?
     *
     * @return bool
     */
    public function canBeShown()
    {
        if ($this->published_at === null) {
            return false;
        }

        return Carbon::parse($this->published_at)->lte(Carbon::now());
    }

    /**
     * Checks if route in specified language code exists
     *
     * @param string $languageCode language code
     *
     * @return bool
     */
    public function hasRoute($languageCode)
    {
        return $this->routes()->where('language_code', $languageCode)->exists();
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
     * Returns tree path to handle block load
     *
     * @return array
     */
    public function getTreePath(): array
    {
        return explode('/', rtrim($this->path, '/'));
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
        $path = Route::buildUniquePath($this->getSlug($translation), $translation->language_code);

        $route = Route::firstOrNew(
            [
                'language_code' => $translation->language_code,
                'path'          => $path,
            ],
            ['is_active' => $isActive]
        );

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
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function setTypeAttribute($type)
    {
        if (!$type instanceof ContentType) {
            $type = ContentType::getByName($type);
        }
        if (!$type) {
            throw new InvalidArgumentException('Unknown content type');
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
     * @throws DomainException
     *
     * @return ContentTypeHandler
     */
    protected function getHandler()
    {
        $handler = resolve($this->type->handler);
        if (!$handler instanceof ContentTypeHandler) {
            throw new DomainException("Type: $this->type can't be handled");
        }
        return $handler;
    }

    /**
     * @param Tree|Content $parent Parent category
     *
     * @throws DomainException
     *
     * @return Content|Tree
     */
    public function setChildOf(Tree $parent)
    {
        if ($parent->type->name !== 'category') {
            throw new DomainException("Content can be assigned only to category parent");
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
