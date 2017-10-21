<?php namespace Gzero\Cms\Model\Presenter;

use Gzero\Base\Model\Presenter\BasePresenter;

class ContentTranslationPresenter extends BasePresenter {

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
            $alternativeField = config('gzero.seo.alternative_title', 'title');
        }
        $text = $this->removeNewLinesAndWhitespace($this->$alternativeField);
        // if alternative field is not empty
        if ($text) {
            return $this->seo_title ? $this->removeNewLinesAndWhitespace($this->seo_title) : $text;
        }
        // show site name as default
        return option('general', 'site_name');
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
        $descLength = option('seo', 'desc_length', config('gzero.seo.desc_length', 160));
        if (!$alternativeField) {
            $alternativeField = config('gzero.seo.alternative_desc', 'body');
        }
        // if SEO description is set
        if ($this->seo_description) {
            return $this->removeNewLinesAndWhitespace($this->seo_description);
        } else {
            $text = $this->removeNewLinesAndWhitespace($this->$alternativeField);
            // if alternative field is not empty
            if ($text) {
                return strlen($text) >= $descLength ? substr($text, 0, strpos($text, ' ', $descLength)) : $text;
            };
            // show site description as default
            return option('general', 'site_desc');
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
