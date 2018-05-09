<?php namespace Gzero\Cms\Repositories;

use Carbon\Carbon;
use Gzero\Cms\Models\Content;
use Gzero\Core\Models\Language;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\Repositories\ReadRepository;
use Gzero\Core\Services\LanguageService;
use Gzero\InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as RawBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

class ContentReadRepository implements ReadRepository {

    /** @var array */
    public static $loadRelations = [
        'author',
        'routes',
        'thumb',
        'translations',
        'type'
    ];

    /**
     * Retrieve a content by given id
     *
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return $this->loadRelations(Content::find($id));
    }

    /**
     * Retrieve single softDeleted entity
     *
     * @param integer $id Entity id
     *
     * @return mixed
     */
    public function getDeletedById($id)
    {
        return $this->loadRelations(Content::onlyTrashed()->find($id));
    }

    /**
     * Retrieve single softDeleted entity
     *
     * @param integer $id Entity id
     *
     * @return mixed
     */
    public function getByIdWithTrashed($id)
    {
        return $this->loadRelations(Content::withTrashed()->find($id));
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|\Illuminate\Support\Collection|static
     * @throws InvalidArgumentException
     */
    public function getTree(QueryBuilder $builder)
    {
        $query = Content::with(self::$loadRelations);

        if (optional($builder->getFilter('only_categories'))->getValue()) {
            $query->join('content_types as ct', 'contents.type_id', '=', 'ct.id');
            $query->where('ct.name', 'category');
        }

        $builder = (new QueryBuilder())->orderBy('translations.title', 'asc');

        $query->orderBy('level', 'ASC');

        $builder->applySorts($query, 'contents');

        $results = $query->distinct()->get(['contents.*']);
        $trees   = (new Content)->buildTree($results, true);
        if ($trees === null) {
            return collect([]);
        }
        if (!$trees instanceof Collection) {
            return collect([$trees]);
        }
        return $trees;
    }

    /**
     * Retrieve a content by given path
     *
     * @param string $path         URI path
     * @param string $languageCode Language code
     * @param bool   $onlyActive   Trigger
     *
     * @return Content|mixed
     */
    public function getByPath(string $path, string $languageCode, bool $onlyActive = false)
    {
        return Content::query()
            ->with(self::$loadRelations)
            ->join('routes', function ($join) use ($languageCode, $path, $onlyActive) {
                $join->on('contents.id', '=', 'routes.routable_id')
                    ->where('routes.routable_type', '=', Content::class)
                    ->where('routes.language_code', $languageCode)
                    ->where('routes.path', $path)
                    ->when($onlyActive, function ($query) {
                        $query->where('routes.is_active', true);
                    });
            })
            ->first(['contents.*']);
    }

    /**
     * Returns titles & url paths from ancestors
     *
     * @param Content  $content    Content
     * @param Language $language   Language
     * @param bool     $onlyActive isActive trigger
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAncestorsTitlesAndPaths(Content $content, Language $language, $onlyActive = true)
    {
        $ancestorIds = array_filter(explode('/', $content->path));
        return \DB::table('contents as c')
            ->join('routes as r', function ($join) use ($language, $onlyActive) {
                $join->on('c.id', '=', 'r.routable_id')
                    ->where('r.routable_type', '=', Content::class)
                    ->where('r.language_code', $language->code)
                    ->when($onlyActive, function ($query) {
                        $query->where('r.is_active', true);
                    });
            })
            ->join('content_translations as ct', function ($join) use ($language, $onlyActive) {
                $join->on('c.id', '=', 'ct.content_id')->where('ct.language_code', $language->code)
                    ->when($onlyActive, function ($query) {
                        $query->where('ct.is_active', true);
                    });
            })
            ->whereIn('c.id', $ancestorIds)
            ->orderBy('level', 'ASC')
            ->select(['ct.title', 'r.path'])
            ->get();
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getMany(QueryBuilder $builder)
    {
        return $this->getManyFrom(Content::query(), $builder);
    }

    /**
     * @param Content      $content Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyChildren(Content $content, QueryBuilder $builder)
    {
        return $this->getManyFrom($content->children()->newQuery()->getQuery(), $builder);
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyDeleted(QueryBuilder $builder)
    {
        return $this->getManyFrom(Content::query()->onlyTrashed(), $builder);
    }

    /**
     * Get only publicly accessible data on published content.
     *
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyPublished(QueryBuilder $builder)
    {
        return $this->getManyPublishedFrom(Content::query()->with('type'), $builder);
    }

    /**
     * @param Content      $content Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyPublishedChildren(Content $content, QueryBuilder $builder)
    {
        return $this->getManyPublishedFrom($content->children()->newQuery()->with('type')->getQuery(), $builder);
    }

    /**
     * Get only publicly accessible data on published content.
     *
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyNotPublished(QueryBuilder $builder)
    {
        return $this->getManyNotPublishedFrom(Content::query()->with('type'), $builder);
    }

    /**
     * @param Content      $content Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyNotPublishedChildren(Content $content, QueryBuilder $builder)
    {
        return $this->getManyNotPublishedFrom($content->children()->newQuery()->with('type')->getQuery(), $builder);
    }

    /**
     * Get all children specific content
     *
     * @param Content  $content  parent
     * @param Language $language Current language
     *
     * @return mixed
     */
    public function getChildren(Content $content, Language $language)
    {
        return $content->children()
            ->with(self::$loadRelations)
            ->where('published_at', '<=', Carbon::now())
            ->whereHas('routes', function ($query) use ($language) {
                $query->where('routes.is_active', true)
                    ->where('language_code', $language->code);
            })
            ->orderBy('is_promoted', 'DESC')
            ->orderBy('is_sticky', 'DESC')
            ->orderBy('weight', 'ASC')
            ->orderBy('published_at', 'DESC')
            ->paginate(option('general', 'default_page_size', 20));
    }

    /**
     * Get all contents for homepage
     *
     * @param Language $language Current language
     *
     * @return mixed
     */
    public function getForHomepage(Language $language)
    {
        return Content::query()
            ->with(self::$loadRelations)
            ->where('published_at', '<=', Carbon::now())
            ->where('is_on_home', '=', true)
            ->whereHas('routes', function ($query) use ($language) {
                $query->where('routes.is_active', true)
                    ->where('language_code', $language->code);
            })
            ->orderBy('is_promoted', 'DESC')
            ->orderBy('is_sticky', 'DESC')
            ->orderBy('weight', 'ASC')
            ->orderBy('published_at', 'DESC')
            ->paginate(option('general', 'default_page_size', 20));
    }

    /**
     * Eager load relations
     *
     * @param Content|Collection $model Model or collection
     *
     * @return Content|Collection
     */
    public function loadRelations($model)
    {
        return optional($model)->load(self::$loadRelations);
    }

    /**
     * @param Builder|RawBuilder $query   Eloquent query object
     * @param QueryBuilder       $builder Query builder
     *
     * @return LengthAwarePaginator
     * @throws InvalidArgumentException
     */
    protected function getManyFrom(Builder $query, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $query->with(self::$loadRelations);

        if ($builder->hasRelation('translations')) {
            if (!$builder->getFilter('translations.language_code')) {
                throw new InvalidArgumentException('Language code is required');
            }
            $query->join('content_translations as t', 'contents.id', '=', 't.content_id');
            $builder->applyRelationFilters('translations', 't', $query);
            $builder->applyRelationSorts('translations', 't', $query);
        }

        if ($builder->hasFilter('type') || $builder->hasSort('type')) {
            $query->join('content_types as ct', 'contents.type_id', '=', 'ct.id');
            optional($builder->getFilter('type'))->apply($query, 'ct', 'name');
            optional($builder->getSort('type'))->apply($query, 'ct', 'name');
        }

        $builder->applyFilters($query, 'contents');
        $builder->applySorts($query, 'contents');

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['contents.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('contents.id')->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * Get all content translations for specified content.
     *
     * @param Content      $content Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyTranslations(Content $content, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $content->translations(false)->newQuery()->getQuery();

        $builder->applyFilters($query, 'content_translations');
        $builder->applySorts($query, 'content_translations');

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['content_translations.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('content_translations.id')->get()->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * @param Builder|RawBuilder $query   Eloquent query object
     * @param QueryBuilder       $builder Query builder
     *
     * @return LengthAwarePaginator
     */
    public function getManyPublishedFrom(Builder $query, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $query->where('published_at', '<=', Carbon::now())
            ->join('routes as r', function ($join) {
                $join->on('contents.id', '=', 'r.routable_id')
                    ->where('r.routable_type', '=', Content::class)
                    ->where('r.is_active', true);
            })
            ->distinct();

        if ($builder->hasFilter('type') || $builder->hasSort('type')) {
            $query->join('content_types as ct', 'contents.type_id', '=', 'ct.id');
            optional($builder->getFilter('type'))->apply($query, 'ct', 'name');
            optional($builder->getSort('type'))->apply($query, 'ct', 'name');
        }

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['contents.*']);

        $results->load(
            array_merge(
                self::$loadRelations,
                [
                    'routes' => function ($query) {
                        $query->where('is_active', true);
                    }
                ]
            )
        );

        $results->transform(function ($content) {
            $languages            = $content->routes->pluck('language_code');
            $filteredTranslations = $content->translations->filter(function ($translation) use ($languages) {
                return $languages->contains($translation->language_code);
            });
            $content->setRelation('translations', $filteredTranslations);
            return $content;
        });

        return new LengthAwarePaginator(
            $results,
            $count->select('contents.id')->get()->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * @param Builder|RawBuilder $query   Eloquent query object
     * @param QueryBuilder       $builder Query builder
     *
     * @return LengthAwarePaginator
     */
    public function getManyNotPublishedFrom(Builder $query, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $query->where('published_at', '<=', Carbon::now())
            ->join('routes as r', function ($join) {
                $join->on('contents.id', '=', 'r.routable_id')
                    ->where('r.routable_type', '=', Content::class)
                    ->where('r.is_active', false);
            })
            ->distinct();

        if ($builder->hasFilter('type') || $builder->hasSort('type')) {
            $query->join('content_types as ct', 'contents.type_id', '=', 'ct.id');
            optional($builder->getFilter('type'))->apply($query, 'ct', 'name');
            optional($builder->getSort('type'))->apply($query, 'ct', 'name');
        }

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['contents.*']);

        $results->load(
            array_merge(
                self::$loadRelations,
                [
                    'routes' => function ($query) {
                        $query->where('is_active', false);
                    }
                ]
            )
        );

        $results->transform(function ($content) {
            $languages            = $content->routes->pluck('language_code');
            $filteredTranslations = $content->translations->filter(function ($translation) use ($languages) {
                return $languages->contains($translation->language_code);
            });
            $content->setRelation('translations', $filteredTranslations);
            return $content;
        });

        return new LengthAwarePaginator(
            $results,
            $count->select('contents.id')->get()->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * Get all files with translations for specified content.
     *
     * @param Content      $content Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyFiles(Content $content, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $content->files(false)->with(['type', 'translations']);

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['files.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('files.id')->get()->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }
}
