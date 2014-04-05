<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class UploadTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class UploadTranslation {

    private $langCode;

    /**
     * @param string $langCode
     */
    public function setLangCode($langCode)
    {
        $this->langCode = $langCode;
    }

    /**
     * @return string
     */
    public function getLangCode()
    {
        return $this->langCode;
    }

} 
