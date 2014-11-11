<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;
use Gzero\Doctrine2Extensions\Tree\TreeNode;
use Gzero\Doctrine2Extensions\Tree\TreeNodeTrait;
use Doctrine\ORM\Mapping as ORM;

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
 * @ORM\Entity(repositoryClass="Gzero\Repository\ContentRepository") @ORM\HasLifecycleCallbacks
 */
class Content implements TreeNode {

    use TreeNodeTrait;
    use TimestampTrait;

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ContentType")
     * @ORM\JoinColumn(name="type", referencedColumnName="name")
     * @var ContentType
     */
    protected $type;

    /**
     * @ORM\Column(name="type")
     */
    protected $typeName;

    /**
     * @ORM\OneToMany(targetEntity="ContentTranslation", mappedBy="content", cascade={"persist", "remove"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id")
     * @var User
     **/
    protected $author;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $isActive = false;

    /**
     * @ORM\Column(type="integer")
     * @var boolean
     */
    protected $weight = 0;

    /**
     * Content entity constructor
     *
     * @param ContentType $type Content type entity
     */
    public function __construct(ContentType $type)
    {
        $this->type         = $type;
        $this->children     = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Get entity id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get content Type
     *
     * @return ContentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get content type name
     * NOTE: Without lazy loading
     *
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
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
     * Get content weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set content weight
     *
     * @param int $weight Content weight
     *
     * @return void
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Add BlockTranslation entity
     *
     * @param ContentTranslation $translation ContentTranslation entity
     *
     * @return void
     */
    public function addTranslation(ContentTranslation $translation)
    {
        $this->translations->add($translation);
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
}
