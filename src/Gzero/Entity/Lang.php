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
 * @Entity
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
    private $is_enabled = FALSE;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    private $is_default = FALSE;

    /**
     * @param string $code
     * @param string $i18n
     */
    function __construct($code, $i18n)
    {
        $this->code = $code;
        $this->i18n = $i18n;
    }

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
     * @param mixed $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }

    /**
     * @return mixed
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * @param mixed $is_enabled
     */
    public function setIsEnabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;
    }

    /**
     * @return mixed
     */
    public function getIsEnabled()
    {
        return $this->is_enabled;
    }

}
