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
     * @param OptionRepository $repo
     */
    public function __construct(OptionRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->repository->getCategories();
    }

    /**
     * @param $categoryKey
     *
     * @return array
     */
    public function getOptions($categoryKey)
    {
        return $this->repository->getOptions($categoryKey);
    }

    /**
     * @param $categoryKey
     * @param $optionKey
     *
     * @return array
     */
    public function getOption($categoryKey, $optionKey)
    {
        return json_decode($this->repository->getOption($categoryKey, $optionKey), true);
    }

}
