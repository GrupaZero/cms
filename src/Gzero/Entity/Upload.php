<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Upload
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class Upload {

    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="UploadType")
     * @JoinColumn(name="type", referencedColumnName="name")
     * @var UploadType
     **/
    protected $type;

    /**
     * @OneToMany(targetEntity="UploadTranslation", mappedBy="upload", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $path;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $mime;

    /**
     * @Column(type="string")
     * @var integer
     */
    protected $size;

    /**
     * @param UploadType $type
     */
    public function __construct(UploadType $type)
    {
        $this->type         = $type;
        $this->translations = new ArrayCollection();
    }

    /**
     * @param UploadTranslation $translation
     */
    public function addTranslation(UploadTranslation $translation)
    {
        $translation->setUpload($this);
        $this->translations->add($translation);
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
     * @param \Gzero\Entity\UploadType $type
     */
    public function setType(UploadType $type)
    {
        $this->type = $type;
    }

    /**
     * @return \Gzero\Entity\UploadType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $mime
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    //-----------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //-----------------------------------------------------------------------------------------------

}
