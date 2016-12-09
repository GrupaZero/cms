<?php namespace Gzero\Core;

use Gzero\Repository\OptionRepository;

class OptionsService {

    /**
     * @var OptionRepository
     */
    protected $repository;

    /**
     * OptionsService constructor.
     *
     * @param OptionRepository $repo options repository
     */
    public function __construct(OptionRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Return list of all options categories
     *
     * @return array of categories
     */
    public function getCategories()
    {
        return $this->repository->getCategories();
    }

    /**
     * Return all options from given category
     *
     * @param string $categoryKey category key
     *
     * @return array of options
     */
    public function getOptions($categoryKey)
    {
        return $this->repository->getOptions($categoryKey);
    }

    /**
     * Return a single option
     *
     * @param string $categoryKey category key
     * @param string $optionKey   option key
     *
     * @return string option value
     */
    public function getOption($categoryKey, $optionKey)
    {
        return $this->repository->getOption($categoryKey, $optionKey);
    }

}
