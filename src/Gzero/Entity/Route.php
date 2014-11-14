<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Route
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @ORM\Entity(repositoryClass="Gzero\Repository\RouteRepository")
 */
class Route {

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var string
     */
    public $id;

    /**
     * @ORM\OneToMany(targetEntity="RouteTranslation", mappedBy="route", cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @ORM\OneToOne(targetEntity="Content", inversedBy="route", fetch="EAGER")
     * @ORM\JoinColumn(name="contentId", referencedColumnName="id")
     * @var Content
     **/
    protected $content;

    /**
     * Route entity constructor
     *
     * @param Content $content Content entity
     */
    public function __construct(Content $content)
    {
        $this->content      = $content;
        $this->translations = new ArrayCollection();
    }

    //------------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * Get entity id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get content
     *
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get link translations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Set content
     *
     * @param Content $content Content entity
     *
     * @return void
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }

    //-----------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //-----------------------------------------------------------------------------------------------

}
