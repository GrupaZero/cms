<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Tree\TreeNode;
use Gzero\Doctrine2Extensions\Tree\TreeNodeTrait;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Menu
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity(repositoryClass="Gzero\Repository\MenuLinkRepository") @HasLifecycleCallbacks
 */
class MenuLink implements TreeNode {

    use TreeNodeTrait;
    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var string
     */
    public $id;

    /**
     * @OneToMany(targetEntity="MenuLinkTranslation", mappedBy="menuLink", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $target = '_self';

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $weight = 0;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $isActive = FALSE;

    /**
     * @Column(type="object", nullable=TRUE)
     * @var \stdClass
     */
    protected $options;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->children     = new ArrayCollection();
    }

    /**
     * @param MenuLinkTranslation $translation
     */
    public function addTranslation(MenuLinkTranslation $translation)
    {
        $this->translations->add($translation);
    }

    //------------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param boolean $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    //-----------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //-----------------------------------------------------------------------------------------------
}
