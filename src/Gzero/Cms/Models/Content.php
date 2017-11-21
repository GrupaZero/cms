<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\BaseTree;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Gzero\Cms\Models\Presenter\ContentPresenter;
use Gzero\Core\Exception;
use Illuminate\Http\Response;
use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends BaseTree implements PresentableInterface, Uploadable, Routable {

    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'theme',
        'path',
        'weight',
        'rating',
        'visits',
        'is_on_home',
        'is_comment_allowed',
        'is_promoted',
        'is_sticky',
        'is_active',
        'published_at'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_on_home'         => false,
        'is_comment_allowed' => false,
        'is_promoted'        => false,
        'is_sticky'          => false,
        'is_active'          => false
    ];

    /**
     * @var array
     */
    protected $dates = ['published_at', 'deleted_at'];

    /**
     * Get Content url in specified language.
     * WARNING: This function use LAZY LOADING to get this information
     *
     * @param string $langCode Lang code
     *
     * @return mixed
     * @throws Exception
     */
    public function getUrl($langCode)
    {
        $routeTranslation = $this->route->translations->filter(
            function ($translation) use ($langCode) {
                return $translation->language_code == $langCode;
            }
        )->first();
        if (empty($routeTranslation->url)) {
            throw new Exception("No route [$langCode] translation found for Content id: " . $this->getKey());
        }
        return $routeTranslation->url;
    }

    /**
     * Content type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(ContentType::class, 'name', 'type');
    }

    /**
     * Polymorphic relation with route
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function route()
    {
        return $this->morphOne(Route::class, 'routable');
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
     * Set the contents's type.
     *
     * @param  string $value Type
     *
     * @return void
     * @throws Exception
     */
    public function setTypeAttribute($value)
    {
        if (!$this->isValidType($value)) {
            throw new Exception("The '$value' is an invalid content type.");
        }

        $this->attributes['type'] = $value;
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
     * @param Route    $route Route
     * @param Language $lang  Language
     *
     * @return Response
     */
    public function handle(Route $route, Language $lang): Response
    {
        return response('content?');
    }

    /**
     * Creates route with unique path based on content translation title, and tree hierarchy
     *
     * @param ContentTranslation $translation Translation
     *
     * @return $this
     */
    public function createRouteWithUniquePath(ContentTranslation $translation)
    {
        $route            = $this->route()->first() ?: new Route();
        $routeTranslation = $route->translations()->firstOrNew(
            [
                'route_id'      => $route->id,
                'language_code' => $translation->language_code,
                'is_active'     => true
            ]
        );

        $routeTranslation->path = $this->getUniquePath($translation);

        $this->route()->save($route);
        $route->translations()->save($routeTranslation);

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
     * Returns an unique route path address for given translation title
     *
     * @param ContentTranslation $translation Content translation
     *
     * @return string an unique route path address in specified language
     */
    protected function getUniquePath(ContentTranslation $translation)
    {
        // @TODO use parent path

        return Route::buildUniquePath(str_slug($translation->title), $translation->language_code);
    }

    /**
     * Checks if type is valid
     *
     * @param string $type Type to validate
     *
     * @return bool
     */
    protected function isValidType(string $type): bool
    {
        return array_has(config('gzero-cms.content_type'), $type);
    }
}