<?php namespace Gzero\Entity\Presenter;

use Robbo\Presenter\Presenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Entity\Presenter
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class ContentPresenter extends Presenter {

    /**
     * This function get single translation
     *
     * @param string $langCode LangCode
     *
     * @return string
     */
    public function translation($langCode)
    {
        return $this->translations->filter(
            function ($translation) use ($langCode) {
                return $translation->langCode === $langCode;
            }
        )->first();
    }
}
