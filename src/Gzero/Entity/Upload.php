<?php namespace Gzero\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gzero\Entity\UploadTranslation;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Upload
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Upload {

    private $type;
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @param UploadTranslation $translation
     */
    public function addTranslation(UploadTranslation $translation)
    {
        $this->translations->add($translation);
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $langCode
     *
     * @return UploadTranslation
     */
    public function findTranslation($langCode)
    {
        foreach ($this->translations as $key => $trans) {
            if ($trans->getLangCode() == $langCode) {
                return $this->translations->get($key);
            }
        }
    }

    /**
     * @param UploadType $type
     */
    public function setType(UploadType $type)
    {
        $this->type = $type;
    }

    /**
     * @return UploadType
     */
    public function getType()
    {
        return $this->type;
    }
}
