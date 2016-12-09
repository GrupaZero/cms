<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Lang
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Lang extends Base {

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'i18n',
        'is_enabled',
        'is_default'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => false,
        'is_default' => false
    ];

}
