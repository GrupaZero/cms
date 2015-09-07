<?php namespace Gzero\Repository;

use Gzero\Entity\Option;
use Gzero\Entity\OptionCategory;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class OptionRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class OptionRepository {

    /**
     * @var Array Whole options hierarchy.
     *            This array mapps each category name to an array (which may be empty)
     *            mapping param names to their values
     */
    private $options;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * OptionRepository constructor
     *
     * @param CacheManager $cache Cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
        $this->init();
    }

    /**
     * Refresh options cache
     *
     * @return boolean
     */
    public function refresh()
    {
        if ($this->cache->has('options')) {
            $this->cache->forget('options');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all option categories
     *
     * @return array array with category names
     */
    public function getCategories()
    {
        return array_keys($this->options);
    }

    /**
     * Get all options within the given category
     *
     * @param string $categoryKey category key
     *
     * @return array array mapping option keys (within the given category) to their values
     * @throws RepositoryException When queried for non-existent category
     */
    public function getOptions($categoryKey)
    {
        $this->requireCategoryExists($categoryKey);

        return $this->options[$categoryKey];
    }

    /**
     * Check if the given option exists
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return bool
     * @throws RepositoryException When queried for non-existent category
     */
    public function optionExists($categoryKey, $optionKey)
    {
        $this->requireCategoryExists($categoryKey);

        return array_key_exists($optionKey, $this->options[$categoryKey]);
    }

    /**
     * Get the value of the given option.
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return string option value
     * @throws RepositoryException When queried for non-existent option
     */
    public function getOption($categoryKey, $optionKey)
    {
        $this->requireCategoryExists($categoryKey);

        $category = $this->options[$categoryKey];
        if (!array_key_exists($optionKey, $category)) {
            throw new RepositoryException("Option " . $optionKey . " does not exist under the category " . $categoryKey);
        }

        return $category[$optionKey];
    }

    /**
     * Creates new option category
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category
     */
    public function createCategory($categoryKey)
    {
        if (array_key_exists($categoryKey, $this->options)) {
            throw new RepositoryException("category " . $categoryKey . "already exists");
        }

        $this->validateName($categoryKey);

        OptionCategory::create(['key' => $categoryKey]);
        $this->refresh();
        $this->options[$categoryKey] = [];
    }

    /**
     * Create new option
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     * @param string $value       Option value
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category
     */
    public function updateOrCreateOption($categoryKey, $optionKey, $value)
    {
        $this->validateName($optionKey);
        $this->validateValue($value);

        $this->requireCategoryExists($categoryKey);

        Option::updateOrCreate(['categoryKey' => $categoryKey, 'key' => $optionKey], ['value' => $value]);
        $this->refresh();
        $this->options[$categoryKey][$optionKey] = $value;
    }

    /**
     * Delete the given category
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category
     */
    public function deleteCategory($categoryKey)
    {
        $this->requireCategoryExists($categoryKey);

        OptionCategory::destroy($categoryKey);
        $this->refresh();
        unset($this->options[$categoryKey]);
    }

    /**
     * Remove the given option
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category or option
     */
    public function deleteOption($categoryKey, $optionKey)
    {
        $this->requireCategoryExists($categoryKey);

        $category = $this->options[$categoryKey];

        if (!array_key_exists($optionKey, $category)) {
            throw new RepositoryException(
                "given option " . $optionKey . " does not exist within the " . $categoryKey . " category"
            );
        }

        Option::where(['categoryKey' => $categoryKey, 'key' => $optionKey])->delete();
        $this->refresh();
        unset($category[$optionKey]);
    }

    /**
     * Init options from database or cache
     *
     * @return void
     */
    protected function init()
    {
        if ($this->cache->get('options')) {
            $this->options = $this->cache->get('options');
        } else {
            $this->extractCategoriesFromModel(OptionCategory::all());
            $this->extractOptionsFromModel(Option::all());
            $this->cache->forever('options', $this->options);
        }
    }

    /**
     * Extract the actual data from the Eloquent models into simple arrays
     *
     * @param EloquentCollection $optionCategoryModels Eloquent models
     *
     * @return void
     */
    private function extractCategoriesFromModel($optionCategoryModels)
    {
        $this->options = [];
        foreach ($optionCategoryModels as $optionCategoryModel) {
            $this->options[$optionCategoryModel->key] = [];
        }
    }

    /**
     * Extract the actual data from the Eloquent models into simple arrays
     *
     * @param EloquentCollection $optionModels Eloquent models
     *
     * @return void
     */
    private function extractOptionsFromModel($optionModels)
    {
        foreach ($optionModels as $optionModel) {
            $categoryKey = $optionModel->categoryKey;
            $key         = $optionModel->key;
            $value       = $optionModel->value;

            $this->options[$categoryKey][$key] = $value;
        }
    }

    /**
     * Validate the string for name of category or option
     *
     * @param string $name Name to validate
     *
     * @return void
     * @throws RepositoryException
     */
    private function validateName($name)
    {
        if (!is_string($name) || trim($name) === '') {
            throw new RepositoryException();
        }
    }

    /**
     * Validate the string for value of an option
     *
     * @param string $name Value to validate
     *
     * @return void
     * @throws RepositoryException
     */
    private function validateValue($name)
    {
        if (!is_string($name) || trim($name) === '') {
            throw new RepositoryException();
        }
    }

    /**
     * Simply check if the category exist within the internal options aray
     *
     * @param string $categoryKey Category key
     *
     * @return bool
     */
    private function categoryExists($categoryKey)
    {
        return array_key_exists($categoryKey, $this->options);
    }

    /**
     * Make sure the given category exists - raise exception if not
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryException
     */
    private function requireCategoryExists($categoryKey)
    {
        if (!$this->categoryExists($categoryKey)) {
            throw new RepositoryException("category " . $categoryKey . " does not exist");
        }
    }
}
