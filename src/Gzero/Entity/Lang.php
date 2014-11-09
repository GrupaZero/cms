<?php namespace Gzero\Entity;

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
 * @Entity(repositoryClass="Gzero\Repository\LangRepository")
 */
class Lang {

    /**
     * @Id @Column(type="string", length=2)
     * @var string
     */
    private $code;

    /**
     * @Column(type="string", length=5)
     * @var string
     */
    private $i18n;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    private $isEnabled = false;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    private $isDefault = false;

    /**
     * Lang entity constructor
     *
     * @param string $code Lang code
     * @param string $i18n Lang_i18n symbol
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
     * Set i18n code
     *
     * @param mixed $i18n i18n code
     *
     * @return void
     */
    public function setI18n($i18n)
    {
        $this->i18n = $i18n;
    }

    /**
     * Get i18n code
     *
     * @return mixed
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
     * Get isDefault property
     *
     * @return mixed
     */
    public function getIsDefault()
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
     * Get sEnabled property
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
