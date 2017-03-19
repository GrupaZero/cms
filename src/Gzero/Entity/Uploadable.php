<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Uploadable
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2017, Adrian Skierniewski
 */
interface Uploadable {

    /**
     * Files relation
     *
     * @param bool $active is active
     *
     * @return mixed
     */
    public function files($active = true);

    /**
     * Check if entity exists
     *
     * @param int $id entity id
     *
     * @return boolean
     */
    public static function checkIfExists($id);

}
