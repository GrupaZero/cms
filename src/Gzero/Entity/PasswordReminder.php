<?php namespace Gzero\Entity;

use Gzero\Doctrine2Extensions\Timestamp\TimestampTrait;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class PasswordReminder
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class PasswordReminder {

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
    protected $email;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $token;

}
