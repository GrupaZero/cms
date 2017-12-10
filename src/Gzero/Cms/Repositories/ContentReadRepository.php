<?php namespace Gzero\Cms\Repositories;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Models\Language;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\Repositories\ReadRepository;
use Gzero\Core\Repositories\RepositoryValidationException;
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
        return $this->eagerLoad(Content::find($id));
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
        return $this->eagerLoad(Content::onlyTrashed()->find($id));
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
        return $this->eagerLoad(Content::withTrashed()->find($id));
    }

    /**
     * Retrieve a content translation by given id
     *
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getTranslationById($id)
    {
        return ContentTranslation::find($id);
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
     * @throws RepositoryValidationException
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
     * @throws RepositoryValidationException
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyDeleted(QueryBuilder $builder)
    {
        return $this->getManyFrom(Content::query()->onlyTrashed(), $builder);
    }

    /**
     * Get all children specific content
     *
     * @param Content $content parent
     *
     * @return mixed
     */
    public function getChildren(Content $content)
    {
        return $content->children()
            ->with(self::$loadRelations)
            ->orderBy('is_promoted', 'DESC')
            ->orderBy('is_sticky', 'DESC')
            ->orderBy('weight', 'ASC')
            ->orderBy('published_at', 'ASC')
            ->paginate(option('general', 'default_page_size', 20));
    }

    /**
     * Eager load relations
     *
     * @param Content|Collection $model Model or collection
     *
     * @return Content|Collection
     */
    protected function eagerLoad($model)
    {
        return optional($model)->load(self::$loadRelations);
    }

    /**
     * @param Builder|RawBuilder $query   Eloquent query object
     * @param QueryBuilder       $builder Query builder
     *
     * @return LengthAwarePaginator
     * @throws RepositoryValidationException
     */
    protected function getManyFrom(Builder $query, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $query->with(self::$loadRelations);

        if ($builder->hasRelation('translations')) {
            if (!$builder->getFilter('translations.language_code')) {
                throw new RepositoryValidationException('Language code is required');
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

        $builder->applyFilters($query);
        $builder->applySorts($query);

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
}
