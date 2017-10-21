<?php namespace Gzero\Cms\Model;

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
