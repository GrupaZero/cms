<?php namespace Gzero\Entity;

use Gzero\Entity\Traits\Timestamp;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @Entity @HasLifecycleCallbacks
 */
class BlockTranslation {

    use Timestamp;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Block", inversedBy="features")
     * @var Block
     */
    protected $block;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Gzero\Entity\Block $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * @return \Gzero\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }

}
