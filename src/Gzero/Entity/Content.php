<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity
 */
class Content {

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ManyToOne(targetEntity="ContentType")
     * @JoinColumn(name="type_id", referencedColumnName="id")
     * @var ContentType
     */
    protected $type;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="author", referencedColumnName="id")
     * @var User
     **/
    protected $author;

    /**
     * @param ContentType $type
     */
    public function setType(ContentType $type)
    {
        $this->type = $type;
    }

    /**
     * @return ContentType
     */
    public function getType()
    {
        return $this->type;
    }
} 
