<?php namespace Gzero\Cms\Repositories;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\Repositories\ReadRepository;
use Gzero\InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as RawBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

class BlockReadRepository implements ReadRepository {

    /** @var array */
    public static $loadRelations = [
        'author',
        'translations',
        'type'
    ];

    /**
     * Retrieve a block by given id
     *
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return $this->loadRelations(Block::find($id));
    }

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getMany(QueryBuilder $builder)
    {
        return $this->getManyFrom(Block::query(), $builder);
    }

    /**
     * Eager load relations
     *
     * @param Block|Collection $model Model or collection
     *
     * @return Block|Collection
     */
    public function loadRelations($model)
    {
        return optional($model)->load(self::$loadRelations);
    }

    /**
     * Returns all visible blocks
     *
     * @param array    $ids        Array with blocks ids returned from block finder
     * @param Language $language   Language
     * @param bool     $onlyActive Return only active blocks
     *
     * @return Collection
     */
    public function getVisibleBlocks(array $ids, Language $language, $onlyActive = true)
    {
        $query = Block::query()->with(self::$loadRelations);

        if (!empty($ids)) {
            $query->where(function ($query) use ($ids) {
                $query->whereIn('id', $ids)
                    ->orWhere('filter', null);
            });

        } else { // blocks on all pages only
            $query->where('filter', null);
        }

        if ($onlyActive) {
            $query->whereHas('translations', function ($query) use ($language) {
                $query->where('block_translations.is_active', true)
                    ->where('language_code', $language->code);
            })->where('is_active', '=', true);
        }

        $blocks = $query->orderBy('weight', 'ASC')->get();

        return $blocks;
    }

    /**
     * Returns all blocks with filter
     *
     * @param bool $onlyActive Return only active blocks
     *
     * @return Collection
     */
    public function getBlocksWithFilter($onlyActive = true)
    {
        $query = Block::query()->with(self::$loadRelations)
            ->where('filter', '!=', null);

        if ($onlyActive) {
            $query->where('is_active', '=', true);
        }

        $blocks = $query->orderBy('weight', 'ASC')->get();

        return $blocks;
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
            $query->join('block_translations as t', 'blocks.id', '=', 't.block_id');
            $builder->applyRelationFilters('translations', 't', $query);
            $builder->applyRelationSorts('translations', 't', $query);
        }

        if ($builder->hasFilter('type') || $builder->hasSort('type')) {
            $query->join('block_types as bt', 'blocks.type_id', '=', 'bt.id');
            optional($builder->getFilter('type'))->apply($query, 'bt', 'name');
            optional($builder->getSort('type'))->apply($query, 'bt', 'name');
        }

        $builder->applyFilters($query, 'blocks');
        $builder->applySorts($query, 'blocks');

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['blocks.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('blocks.id')->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * Get all block translations for specified content.
     *
     * @param Block        $block   Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyTranslations(Block $block, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $block->translations(false)->newQuery()->getQuery();

        $builder->applyFilters($query, 'block_translations');
        $builder->applySorts($query, 'block_translations');

        $count = clone $query->getQuery();

        $results = $query->limit($builder->getPageSize())
            ->offset($builder->getPageSize() * ($builder->getPage() - 1))
            ->get(['block_translations.*']);

        return new LengthAwarePaginator(
            $results,
            $count->select('block_translations.id')->get()->count(),
            $builder->getPageSize(),
            $builder->getPage()
        );
    }

    /**
     * Get all files with translations for specified content.
     *
     * @param Block        $block   Content model
     * @param QueryBuilder $builder Query builder
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getManyFiles(Block $block, QueryBuilder $builder): LengthAwarePaginator
    {
        $query = $block->files(false)->with('translations')->newQuery()->getQuery();

        $builder->applyFilters($query, 'files');
        $builder->applySorts($query, 'files');

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
