<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;

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
 * @ORM\Entity(repositoryClass="Gzero\Repository\BlockRepository") @ORM\HasLifecycleCallbacks
 */
class Block extends AbstractEntity {

    use TimestampTrait;

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="BlockType")
     * @ORM\JoinColumn(name="type", referencedColumnName="name")
     * @var BlockType
     **/
    protected $type;

    /**
     * @ORM\Column(name="type")
     */
    protected $typeName;

    /**
     * @ORM\ManyToOne(targetEntity="MenuLink")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     * @var MenuLink
     **/
    protected $menu;

    /**
     * @ORM\OneToMany(targetEntity="BlockTranslation", mappedBy="block", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $uploads;

    /**
     * @ORM\Column(type="json_array", nullable=TRUE)
     * @var Array
     */
    protected $regions = null;

    /**
     * @ORM\Column(type="integer")
     * @var boolean
     */
    protected $weight = 0;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $isCacheable = false;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $isActive = false;

    /**
     * @ORM\Column(type="object", nullable=TRUE)
     * @var \stdClass
     */
    protected $options;

    /**
     * Block entity constructor
     *
     * @param BlockType $type BlockType entity
     */
    public function __construct(BlockType $type)
    {
        $this->type         = $type;
        $this->typeName     = $type->getName();
        $this->translations = new ArrayCollection();
        $this->uploads      = new ArrayCollection();
    }

    /**
     * Add BlockTranslation entity
     *
     * @param BlockTranslation $translation BlockTranslation entity
     *
     * @return void
     */
    public function addTranslation(BlockTranslation $translation)
    {
        $this->translations->add($translation);
    }

    /**
     * Add Upload entity
     *
     * @param Upload $upload Upload entity
     *
     * @return void
     */
    public function addUpload(Upload $upload)
    {
        $this->uploads->add($upload);
    }

    /**
     * Add Menu entity
     *
     * @param MenuLink $menu Menu entity
     *
     * @return void
     */
    public function addMenu(MenuLink $menu)
    {
        $this->menu = $menu;
    }

    //------------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * Get entity id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get block type
     *
     * @return \Gzero\Entity\BlockType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get block type name
     *
     * @return string Type name
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * Set regions
     *
     * @param Array $regions Collection of regions
     *
     * @return void
     */
    public function setRegions($regions)
    {
        /** @TODO Some validation */
        $this->regions = $regions;
    }

    /**
     * Get all regions
     *
     * @return Array
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * Set active flag
     *
     * @param boolean $active Active flag
     *
     * @return void
     */
    public function setActive($active)
    {
        $this->isActive = $active;
    }

    /**
     * Check is block active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Set cacheable flag
     *
     * @param boolean $cacheable Cacheable flag
     *
     * @return void
     */
    public function setCacheable($cacheable)
    {
        $this->isCacheable = $cacheable;
    }

    /**
     * Check is block can be cached
     *
     * @return boolean
     */
    public function isCacheable()
    {
        return $this->isCacheable;
    }

    /**
     * Set menu entity for this block
     *
     * @param \Gzero\Entity\MenuLink $menu Menu entity
     *
     * @return void
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    /**
     * Get menu entity for this block
     *
     * @return \Gzero\Entity\MenuLink
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Get translations entities for this block
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get uploads entities for this block
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    /**
     * Set options
     *
     * @param \stdClass $options Options object
     *
     * @return void
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Get options object
     *
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
