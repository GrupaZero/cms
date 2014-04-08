<?php namespace Gzero\Entity\Traits;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * trait SoftDelete
 *
 * @package    Gzero\Entity\Traits
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
trait SoftDelete {

    /**
     * @Column(type="datetime", nullable=TRUE)
     * @var \DateTime
     */
    protected $deleted_at;

    /**
     * @param \DateTime $deleted_at
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

} 
