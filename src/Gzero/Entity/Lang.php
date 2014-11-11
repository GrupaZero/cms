<?php namespace Gzero\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Lang
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @ORM\Entity(repositoryClass="Gzero\Repository\LangRepository")
 */
class Lang {

    /**
     * @ORM\Id @ORM\Column(type="string", length=2)
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=5)
     * @var string
     */
    private $i18n;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $isEnabled = false;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $isDefault = false;

    /**
     * Lang entity constructor
     *
     * @param string $code Lang code eg. "en"
     * @param string $i18n Lang_i18n symbol eg. "en_US"
     */
    public function __construct($code, $i18n)
    {
        $this->code = $code;
        $this->i18n = $i18n;
    }

    //------------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * Get lang code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get i18n code
     *
     * @return string
     */
    public function getI18n()
    {
        return $this->i18n;
    }

    /**
     * Set isDefault property
     *
     * @param boolean $isDefault True or False
     *
     * @return void
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = (boolean) $isDefault;
    }

    /**
     * Check if lang is default
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isEnabled property
     *
     * @param boolean $isEnabled True or False
     *
     * @return void
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = (boolean) $isEnabled;
    }

    /**
     * Check if lang is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
