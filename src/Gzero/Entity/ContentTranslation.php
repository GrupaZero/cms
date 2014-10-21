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
    protected $isActive = FALSE;

    protected $fillable = ['url', 'title', 'body', 'isActive'];

    /**
     * @param Content $content
     * @param Lang    $lang
     * @param User    $user
     */
    function __construct(Content $content, Lang $lang, User $user = NULL)
    {
        $this->content = $content;
        $this->lang    = $lang;
        $this->user    = $user;
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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return \Gzero\Entity\Content
     */
    public function getContent()
    {
        return $this->content;
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

    public function getFillable()
    {
        return $this->fillable;
    }

    public function setFillable(array $fillable)
    {
        $this->fillable = $fillable;
    }

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

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getAttributes(), TRUE)) {
            $this->$key = $value;
        }
    }
    //------------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //------------------------------------------------------------------------------------------------

    public function isFillable($key)
    {
        if (in_array($key, $this->fillable, TRUE)) {
            return TRUE;
        }
        return FALSE;
    }

    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
    }
}
