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
        $text = $this->removeNewLinesAndWhitespace($this->$alternativeField);
        // if alternative field is not empty
        if ($text) {
            return $this->seoTitle ? $this->removeNewLinesAndWhitespace($this->seoTitle) : $text;
        }
        // show site name as default
        return option('general', 'siteName');
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
        $seoDescLength = option('seo', 'seoDescLength', 160);
        if (!$alternativeField) {
            $alternativeField = config('gzero.seoDescriptionAlternativeField', 'body');
        }
        // if SEO description is set
        if ($this->seoDescription) {
            return $this->removeNewLinesAndWhitespace($this->seoDescription);
        } else {
            $text = $this->removeNewLinesAndWhitespace($this->$alternativeField);
            // if alternative field is not empty
            if ($text) {
                return strlen($text) >= $seoDescLength ? substr($text, 0, strpos($text, ' ', $seoDescLength)) : $text;
            };
            // show site description as default
            return option('general', 'siteDesc');
        }
    }

    /**
     * Function removes new lines and strip whitespace (or other characters) from the beginning and end of a string
     *
     * @param string $string  string to replace
     * @param string $replace replacement
     *
     * @return string
     */
    private function removeNewLinesAndWhitespace($string, $replace = " ")
    {
        return str_replace(["\n\r", "\n\n", "\n", "\r"], $replace, trim(strip_tags($string)));
    }
}
