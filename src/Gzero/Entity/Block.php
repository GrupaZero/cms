<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Entity\Traits\SoftDelete;
use Gzero\Entity\Traits\Timestamp;

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

    use Timestamp;
    use SoftDelete;

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
     * @Column(type="json_array", nullable=TRUE)
     * @var Array
     */
    protected $region;

    /**
     * @OneToMany(targetEntity="BlockTranslation", mappedBy="block")
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $uploads;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $is_cacheable = FALSE;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $is_active = FALSE;

    /**
     * @Column(type="object")
     * @var \stdClass
     */
    protected $options;

    public function __construct(BlockType $type)
    {
        $this->type         = $type;
        $this->translations = new ArrayCollection();
        $this->uploads      = new ArrayCollection();
    }

    //-----------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //-----------------------------------------------------------------------------------------------
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Gzero\Entity\BlockType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \Gzero\Entity\BlockType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $translation
     */
    public function setTranslations($translation)
    {
        $this->translations = $translation;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
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
     * @param Array $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return Array
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $upload
     */
    public function setUploads($upload)
    {
        $this->uploads = $upload;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    //-----------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //-----------------------------------------------------------------------------------------------


}
