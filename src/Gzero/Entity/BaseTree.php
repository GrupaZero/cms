<?php namespace Gzero\Entity;

use Gzero\Entity\traits\DatesFormatTrait;
use Gzero\EloquentTree\Model\Tree;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BaseTree
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
abstract class BaseTree extends Tree {

    use DatesFormatTrait;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Check if file exists
     *
     * @param int $entityId file id
     *
     * @return boolean
     */
    public static function checkIfExists($entityId): bool
    {
        return self::where('id', $entityId)->exists();
    }
}
