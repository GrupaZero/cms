<?php namespace Gzero\Cms\Repositories;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\Repositories\ReadRepository;
use Gzero\Core\Repositories\RepositoryValidationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ContentReadRepository implements ReadRepository {

    /**
     * Retrieve a content by given id
     *
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return Content::find($id);
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
            ->join(
                'routes',
                function ($join) {
                    $join->on('contents.id', '=', 'routes.routable_id')
                        ->where('routes.routable_type', '=', Content::class);
                }
            )
            ->join(
                'route_translations',
                function ($join) use ($languageCode, $path, $onlyActive) {
                    $join->on('routes.id', '=', 'route_translations.route_id')
                        ->where('route_translations.language_code', $languageCode)
                        ->where('route_translations.path', $path);
                    if ($onlyActive) {
                        $join->where('route_translations.is_active', true);
                    }
                }
            )
            ->first(['contents.*']);
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
        $query = Content::query();

        if ($builder->hasRelation('translations')) {
            if (!$builder->getRelationFilter('translations', 'language_code')) {
                throw new RepositoryValidationException('Language code is required');
            }
            $query->join('content_translations as t', 'contents.id', '=', 't.content_id');
            $builder->applyRelationFilters('translations', 't', $query);
            $builder->applyRelationSorts('translations', 't', $query);
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
            ->orderBy('is_promoted', 'DESC')
            ->orderBy('is_sticky', 'DESC')
            ->orderBy('weight', 'ASC')
            ->orderBy('published_at', 'ASC')
            ->paginate(option('general', 'default_page_size', 20));
    }
}
