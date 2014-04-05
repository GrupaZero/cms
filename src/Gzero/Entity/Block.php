<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @var integer
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

    private $translation;

    private $upload;

    public function __construct()
    {
        $this->translation = new ArrayCollection();
        $this->upload      = new ArrayCollection();
    }

    /**
     * @param BlockUpload $upload
     */
    public function addUpload(BlockUpload $upload)
    {
        $this->upload->add($upload);
    }

    /**
     * @return ArrayCollection
     */
    public function getUploadsCollection()
    {
        return $this->upload;
    }

    /**
     * @param BlockTranslation $translation
     */
    public function addTranslation(BlockTranslation $translation)
    {
        $this->translation->add($translation);
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslationsCollection()
    {
        return $this->translation;
    }

    /**
     * @param int $id
     *
     * @return BlockTranslation
     */
    public function findTranslation($id = 1)
    {
        return $this->translation->first();
    }

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

    /**
     * @param \DateTime $deleted_at
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

}
