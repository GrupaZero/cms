<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

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
 * @Entity @HasLifecycleCallbacks
 */
class Block extends AbstractEntity {

    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="BlockType")
     * @JoinColumn(name="type", referencedColumnName="name")
     * @var BlockType
     **/
    protected $type;

    /**
     * @ManyToOne(targetEntity="MenuLink")
     * @JoinColumn(name="menu_id", referencedColumnName="id")
     * @var MenuLink
     **/
    protected $menu;

    /**
     * @OneToMany(targetEntity="BlockTranslation", mappedBy="block", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $uploads;
    /**
     * @Column(type="json_array", nullable=TRUE)
     * @var Array
     */
    protected $regions;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $isCacheable = FALSE;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $isActive = FALSE;

    /**
     * @Column(type="object")
     * @var \stdClass
     */
    protected $options;

    /**
     * @param BlockType $type
     */
    public function __construct(BlockType $type)
    {
        $this->type         = $type;
        $this->translations = new ArrayCollection();
        $this->uploads      = new ArrayCollection();
    }

    /**
     * @param BlockTranslation $translation
     */
    public function addTranslation(BlockTranslation $translation)
    {
        $this->translations->add($translation);
    }

    /**
     * @param Upload $upload
     */
    public function addUpload(Upload $upload)
    {
        $this->uploads->add($upload);
    }

    /**
     * @param MenuLink $menu
     */
    public function addMenu(MenuLink $menu)
    {
        $this->menu = $menu;
    }

    //------------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Gzero\Entity\BlockType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Array $regions
     */
    public function setRegions($regions)
    {
        /** @TODO Some validation */
        $this->regions = $regions;
    }

    /**
     * @return Array
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * @param $active
     */
    public function setActive($active)
    {
        $this->isActive = $active;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $cacheable
     */
    public function setCacheable($cacheable)
    {
        $this->isCacheable = $cacheable;
    }

    /**
     * @return boolean
     */
    public function isCacheable()
    {
        return $this->isCacheable;
    }

    /**
     * @param \Gzero\Entity\MenuLink $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return \Gzero\Entity\MenuLink
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUploads()
    {
        return $this->uploads;
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

    //-----------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //-----------------------------------------------------------------------------------------------

}
