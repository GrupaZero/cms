<?php namespace Gzero\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Lang
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Lang extends Base {

    public $incrementing = false;

    protected $primaryKey = 'code';

    protected $fillable = [
        'code',
        'i18n',
        'isEnabled',
        'isDefault'
    ];

}
