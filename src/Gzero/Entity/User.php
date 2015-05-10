<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class User extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'email',
        'firstName',
        'lastName',
        'password',
        'hasSocialIntegrations' /**@TODO proper method for adding new fillable fields from package with migrations*/
    ];
}
