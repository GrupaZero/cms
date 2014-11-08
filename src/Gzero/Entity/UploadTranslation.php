<?php namespace Gzero\Entity;

use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class UploadTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class UploadTranslation {

    use TimestampTrait;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Upload", inversedBy="translations")
     * @var Upload
     */
    protected $upload;

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
    protected $name;

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
     * @param \Gzero\Entity\Lang $lang
     */
    public function setLang(Lang $lang)
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
     * @param \Gzero\Entity\Upload $upload
     */
    public function setUpload(Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * @return \Gzero\Entity\Upload
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * @param \Gzero\Entity\User $user
     */
    public function setUser(User $user)
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    //-----------------------------------------------------------------------------------------------
    // END:  Getters & Setters
    //-----------------------------------------------------------------------------------------------

}
