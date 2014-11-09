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
     * BlockTranslation constructor
     *
     * @param Block $block Block entity
     * @param Lang  $lang  Lang entity
     *
     * @param User  $user  User entity
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
     * Get entity id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get block entity
     *
     * @return \Gzero\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
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
     * Get lang code
     * NOTE: Without lazy loading
     *
     * @return string
     */
    public function getLangCode()
    {
        return $this->langCode;
    }

    /**
     * Get user entity
     *
     * @return \Gzero\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set sites property
     *
     * @param string $sites Sites string
     *
     * @return void
     */
    public function setSites($sites)
    {
        $this->sites = $sites;
    }

    /**
     * Get sites property
     *
     * @return string
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * Set title
     *
     * @param string $title Block title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * Set content body
     *
     * @param string $body Block body
     *
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get Block body
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
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

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
