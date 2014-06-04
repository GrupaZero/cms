<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;
use Gzero\Doctrine2Extensions\Tree\TreeNode;
use Gzero\Doctrine2Extensions\Tree\TreeNodeTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity(repositoryClass="Gzero\Repository\ContentRepository") @HasLifecycleCallbacks
 */
class Content implements TreeNode {

    use TreeNodeTrait;
    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ContentType")
     * @JoinColumn(name="type", referencedColumnName="name")
     * @var ContentType
     */
    protected $type;

    /**
     * @OneToMany(targetEntity="ContentTranslation", mappedBy="content", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="author", referencedColumnName="id")
     * @var User
     **/
    protected $author;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $isActive = FALSE;

    /**
     * @Column(type="integer")
     * @var boolean
     */
    protected $weight = 0;

    public function __construct(ContentType $type)
    {
        $this->type         = $type;
        $this->children     = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ContentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->isActive = $active;
    }

    /**
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @param ContentTranslation $translation
     */
    public function addTranslation(ContentTranslation $translation)
    {
        $this->translations->add($translation);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }
} 
