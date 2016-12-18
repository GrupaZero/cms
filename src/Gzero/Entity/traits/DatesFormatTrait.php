<?php namespace Gzero\Entity\traits;

use Carbon\Carbon;

trait DatesFormatTrait {

    /**
     * Changes the created_at database 'Y-m-d H:i:s' to 'd-m-Y - H:s'
     *
     * @param string $date created_at date
     *
     * @return string formatted date
     *
     */
    public function getCreatedAtAttribute($date)
    {
        return $this->getFormattedDate($date);
    }

    /**
     * Changes the updated_at database 'Y-m-d H:i:s' to 'd-m-Y - H:s'
     *
     * @param string $date updated_at date
     *
     * @return string formatted date
     *
     */
    public function getUpdatedAttribute($date)
    {
        return $this->getFormattedDate($date);
    }

    /**
     * Changes the deleted_at database 'Y-m-d H:i:s' to 'd-m-Y - H:s'
     *
     * @param string $date deleted_at date
     *
     * @return string formatted date
     *
     */
    public function getDeletedAttribute($date)
    {
        return $this->getFormattedDate($date);
    }

    /**
     * Changes the published_at database 'Y-m-d H:i:s' to 'd-m-Y - H:s'
     *
     * @param string $date published_at date
     *
     * @return string formatted date
     *
     */
    public function getPublishedAtAttribute($date)
    {
        return $this->getFormattedDate($date);
    }

    /**
     * Changes the date 'Y-m-d H:i:s' format to 'd-m-Y - H:s'
     *
     * @param string $date date to change
     *
     * @return string formatted date
     *
     */
    private function getFormattedDate($date)
    {
        if (!$date) {
            return $date;
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y - H:s');
    }
}
