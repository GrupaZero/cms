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
     * @param string $code
     * @param string $i18n
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $i18n
     */
    public function setI18n($i18n)
    {
        $this->i18n = $i18n;
    }

    /**
     * @return mixed
     */
    public function getI18n()
    {
        return $this->i18n;
    }

    /**
     * @param mixed $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @return mixed
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param mixed $isEnabled
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @return mixed
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
