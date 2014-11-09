<?php namespace Gzero\Entity;

use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;
use ReflectionClass;
use ReflectionProperty;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class ContentTranslation {

    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $url;

    /**
     * @ManyToOne(targetEntity="Content", inversedBy="translations")
     * @var Content
     */
    protected $content;

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

    protected $fillable = ['url', 'title', 'body', 'isActive'];

    /**
     * ContentTranslation
     *
     * @param Content $content Content entity
     * @param Lang    $lang    Lang entity
     * @param User    $user    User entity
     */
    public function __construct(Content $content, Lang $lang, User $user = null)
    {
        $this->content = $content;
        $this->lang    = $lang;
        $this->user    = $user;
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
     * Get url address
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set Url address
     *
     * @param string $url Url address
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get content entity
     * NOTE: Without lazy loading
     *
     * @return \Gzero\Entity\Content
     */
    public function getContent()
    {
        return $this->content;
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
     * Get User entity
     *
     * @return \Gzero\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set title
     *
     * @param string $title Content title
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
     * @param string $body Content body
     *
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get content body
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

    // @codingStandardsIgnoreStart

    /**
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * @param array $fillable
     */
    public function setFillable(array $fillable)
    {
        $this->fillable = $fillable;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $reflect    = new ReflectionClass($this);
        $props      = $reflect->getProperties(ReflectionProperty::IS_PROTECTED);
        $attributes = [];
        foreach ($props as $prop) {
            $attributes[] = $prop->getName();
        }
        return $attributes;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getAttributes(), true)) {
            $this->$key = $value;
        }
    }
    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

    /**
     * @param $key
     *
     * @return bool
     */
    public function isFillable($key)
    {
        if (in_array($key, $this->fillable, true)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     */
    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
    }

    // @codingStandardsIgnoreEnd
}
