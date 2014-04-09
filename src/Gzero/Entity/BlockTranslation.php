<?php namespace Gzero\Entity;

use Gzero\Entity\Traits\Timestamp;

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

    use Timestamp;

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
     * @param string $title
     * @param Lang   $lang
     */
    function __construct($title, Lang $lang)
    {
        $this->lang  = $lang;
        $this->title = $title;
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
     * @param \Gzero\Entity\Block $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * @return \Gzero\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @param \Gzero\Entity\Lang $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return \Gzero\Entity\Lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param \Gzero\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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

    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

}
