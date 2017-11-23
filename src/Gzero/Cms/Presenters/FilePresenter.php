<?php namespace Gzero\Cms\Presenters;

use Robbo\Presenter\Presenter;

class FilePresenter extends Presenter {

    /**
     * This function get single translation
     *
     * @param string $langCode LangCode
     *
     * @return string
     */
    public function translation($langCode)
    {
        $translation = '';
        if (!empty($this->translations) && !empty($langCode)) {
            $translation = $this->translations->filter(
                function ($translation) use ($langCode) {
                    return $translation->language_code === $langCode;
                }
            )->first();
        }
        return $translation;
    }

    /**
     * This function returns author first and last name
     *
     * @return string
     */
    public function authorName()
    {
        if (!empty($this->author)) {
            return $this->author->getPresenter()->displayName();
        }
        return trans('common.anonymous');
    }

    /**
     * This function returns file public url
     *
     * @return string
     */
    public function url()
    {
        return $this->getUrl();
    }
}
