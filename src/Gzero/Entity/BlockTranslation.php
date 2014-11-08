<?php namespace Gzero\Entity;

use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class BlockTranslation {

    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Block", inversedBy="translations")
     * @var Block
     */
    protected $block;

    /**
     * @ManyToOne(targetEntity="Lang")
     * @JoinColumn(name="lang", referencedColumnName="code")
     * @var Lang
     **/
    protected $lang;

    /**
     * @Column(name="lang")
     */
    protected $langCode;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user", referencedColumnName="id")
     * @var User
     **/
    protected $user;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $sites = 'all';

    /**
     * @Column(type="string")
     * @var string
     */
    protected $title;

    /**
     * @Column(type="text", nullable=TRUE)
     * @var string
     */
    protected $body;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $isActive = false;

    /**
     * @param Block $block
     * @param Lang  $lang
     *
     * @param User  $user
     */
    public function __construct(Block $block, Lang $lang, User $user = null)
    {
        $this->block = $block;
        $this->lang  = $lang;
        $this->user  = $user;
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
     * @return \Gzero\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @return \Gzero\Entity\Lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getLangCode()
    {
        return $this->langCode;
    }

    /**
     * @return \Gzero\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $sites
     */
    public function setSites($sites)
    {
        $this->sites = $sites;
    }

    /**
     * @return string
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
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

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
