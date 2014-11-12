<?php namespace Gzero\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class RouteTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @ORM\Entity
 */
class RouteTranslation {

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Route", inversedBy="translations")
     * @ORM\JoinColumn(name="routeId", referencedColumnName="id")
     * @var Route
     */
    protected $route;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity="Lang")
     * @ORM\JoinColumn(name="lang", referencedColumnName="code")
     * @var Lang
     **/
    protected $lang;

    /**
     * @ORM\Column(name="lang")
     */
    protected $langCode;

    /**
     * RouteTranslation
     *
     * @param Route $route Content entity
     * @param Lang  $lang  Lang entity
     */
    public function __construct(Route $route, Lang $lang)
    {
        $this->route = $route;
        $this->lang  = $lang;
    }

    //------------------------------------------------------------------------------------------------
    // START: Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * Get entity Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get content entity
     * NOTE: Without lazy loading
     *
     * @return \Gzero\Entity\Content
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get lang entity
     *
     * @return \Gzero\Entity\Lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Get Lang code
     *
     * @return string
     */
    public function getLangCode()
    {
        return $this->langCode;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title Link title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
