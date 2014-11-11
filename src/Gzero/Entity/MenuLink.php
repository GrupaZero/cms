<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Tree\TreeNode;
use Gzero\Doctrine2Extensions\Tree\TreeNodeTrait;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;

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
 * @ORM\Entity(repositoryClass="Gzero\Repository\MenuLinkRepository") @ORM\HasLifecycleCallbacks
 */
class MenuLink implements TreeNode {

    use TreeNodeTrait;
    use TimestampTrait;

    // @codingStandardsIgnoreStart

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var string
     */
    public $id;

    /**
     * @ORM\OneToMany(targetEntity="MenuLinkTranslation", mappedBy="menuLink", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $target = '_self';

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $weight = 0;

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
     * MenuLink entity constructor
     */
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

    // @codingStandardsIgnoreEnd
}
