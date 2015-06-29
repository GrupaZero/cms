<?php namespace Gzero\Entity\Presenter;

use Carbon\Carbon;
use Robbo\Presenter\Presenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentTranslationPresenter
 *
 * @package    Gzero\Entity\Presenter
 * @author     Mateusz Urbanowicz <urbanowiczmateusz89@gmail.com>
 * @copyright  Copyright (c) 2015, Mateusz Urbanowicz
 */
class ContentTranslationPresenter extends Presenter {

    /**
     * Return seoTitle of translation if exists
     * otherwise return generated one
     *
     * @param mixed $alternativeField alternative field to display when seoTitle field is empty
     *
     * @return string
     */
    public function seoTitle($alternativeField = false)
    {
        if (!$alternativeField) {
            $alternativeField = config('gzero.seoTitleAlternativeField', 'title');
        }
        return strip_tags($this->seoTitle ? $this->seoTitle : $this->$alternativeField);
    }

    /**
     * Return seoDescription of translation if exists
     * otherwise return generated one
     *
     * @param mixed $alternativeField alternative field to display when seoDescription field is empty
     *
     * @return string
     */
    public function seoDescription($alternativeField = false)
    {
        if (!$alternativeField) {
            $alternativeField = config('gzero.seoDescriptionAlternativeField', 'body');
        }

        if ($this->seoDescription) {
            return $this->seoDescription;
        } else {
            $text = strip_tags($this->$alternativeField);
            return strlen($text) >= 160 ? substr($text, 0, strpos($text, ' ', 160)) : $text;
        }
    }

}
