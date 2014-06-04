<?php namespace Gzero\Entity;


use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class MenuLinkTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class MenuLinkTranslation {

    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="MenuLink", inversedBy="translations")
     * @var MenuLink
     */
    protected $menuLink;

    /**
     * @ManyToOne(targetEntity="Lang")
     * @JoinColumn(name="lang", referencedColumnName="code")
     * @var Lang
     **/
    protected $lang;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user", referencedColumnName="id")
     * @var User
     **/
    protected $user;

    /**
     * @param MenuLink $menuLink
     * @param Lang     $lang
     *
     * @param User     $user
     *
     * @internal param \Gzero\Entity\Block $block
     */

    /**
     * @Column(type="string")
     * @var string
     */
    protected $url;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $title;

    /**
     * @Column(type="string", nullable=TRUE)
     * @var string
     */
    protected $alt;


    function __construct(MenuLink $menuLink, Lang $lang, User $user = NULL)
    {
        $this->menuLink = $menuLink;
        $this->lang     = $lang;
        $this->user     = $user;
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
     * @return \Gzero\Entity\Lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    /**
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @return \Gzero\Entity\MenuLink
     */
    public function getMenuLink()
    {
        return $this->menuLink;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return \Gzero\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
