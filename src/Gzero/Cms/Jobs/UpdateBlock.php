<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Block;
use Gzero\Core\DBTransactionTrait;

class UpdateBlock {

    use DBTransactionTrait;

    /** @var Block */
    protected $block;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'region',
        'theme',
        'weight',
        'filter',
        'options',
        'is_active',
        'is_cacheable',
    ];

    /**
     * Create a new job instance.
     *
     * @param Block $block      Block model
     * @param array $attributes Array of optional attributes
     */
    public function __construct(Block $block, array $attributes = [])
    {
        $this->block      = $block;
        $this->attributes = array_only($attributes, $this->allowedAttributes);
    }

    /**
     * Execute the job.
     *
     * @return Block
     */
    public function handle()
    {
        $block = $this->dbTransaction(function () {
            $this->block->fill($this->attributes);
            $this->block->save();

            event('block.updated', [$this->block]);
            return $this->block;
        });
        return $block;
    }
}
