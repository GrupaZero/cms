<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Block
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @Table(name="blocks")
 */
class Block {

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var string
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="BlockType")
     * @JoinColumn(name="type_id", referencedColumnName="id")
     * @var BlockType
     **/
    private $type;

    /**
     * @ManyToOne(targetEntity="Menu")
     * @JoinColumn(name="menu_id", referencedColumnName="id")
     * @var Menu
     **/
    private $menu;

    /**
     * @Column(type="json_array")
     * @var Array
     */
    private $region;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    private $is_cacheable;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    private $is_active;

    /**
     * @Column(type="object")
     * @var \stdClass
     */
    private $options;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    private $created_at;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    private $deleted_at;

    /**
     * @param \stdClass $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return \stdClass
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $is_active
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @param boolean $is_cacheable
     */
    public function setIsCacheable($is_cacheable)
    {
        $this->is_cacheable = $is_cacheable;
    }

    /**
     * @return boolean
     */
    public function getIsCacheable()
    {
        return $this->is_cacheable;
    }

    /**
     * @param mixed $menu
     */
    public function setMenu(Menu $menu = NULL)
    {
        $this->menu = $menu;
    }

    /**
     * @return mixed
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param \stdClass $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return \stdClass
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

}
