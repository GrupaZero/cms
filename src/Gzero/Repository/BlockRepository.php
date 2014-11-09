<?php namespace Gzero\Repository;

use Gzero\Entity\Block;
use Gzero\Entity\BlockTranslation;
use Gzero\Entity\BlockType;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class BlockRepository extends BaseRepository {

    /**
     * Get single block with active translations
     *
     * @param int $id Block id
     *
     * @return Block
     */
    public function getById($id)
    {
        $qb = $this->newQB()
            ->select('b,t')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.translations', 't', 'WITH', 't.isActive = 1')
            ->where('b.id = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get block type by id
     *
     * @param int $id Block type id
     *
     * @return BlockType
     */
    public function getTypeById($id)
    {
        return $this->_em->find($this->getTypeClassName(), $id);
    }

    /**
     * Get block translation by id
     *
     * @param int $id Block translation id
     *
     * @return BlockTranslation
     */
    public function getTranslationById($id)
    {
        $qb = $this->newQB()
            ->select('t')
            ->from($this->getTranslationClassName(), 't')
            ->where('t.id = :id')
            ->orderBy('t.isActive')
            ->setParameter('id', $id);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Gets all active blocks with translation in specified lang
     *
     * @param Lang $lang Lang model
     *
     * @return mixed
     */
    public function getAllActive(Lang $lang)
    {
        $qb = $this->newQB();
        $qb->select('b,t')
            ->from($this->getClassName(), 'b')
            // we need only blocks with active translations
            ->innerJoin(
                'b.translations',
                't',
                'WITH',
                $qb->expr()->andx(
                    $qb->expr()->eq('t.lang', ':lang'),
                    $qb->expr()->eq('t.isActive', '1')
                )
            )
            ->where('b.isActive = 1')
            ->where('b.regions IS NOT NULL')
            ->orderBy('b.weight')
            ->setParameter('lang', $lang->getCode());
        return $qb->getQuery()->getResult();
    }

    /**
     * Get all block types
     *
     * @return array
     */
    public function getAllTypes()
    {
        $qb = $this->newQB()
            ->select('t')
            ->from($this->getTypeClassName(), 't');
        return $qb->getQuery()->getResult();
    }

    /**
     * Get all block translations
     *
     * @param Block $block Block entity
     *
     * @return array
     */
    public function getAllTranslations(Block $block)
    {
        $qb = $this->newQB()
            ->select('t')
            ->from($this->getTranslationClassName(), 't')
            ->where('IDENTITY(t.block) = :id')
            ->orderBy('t.isActive')
            ->setParameter('id', $block->getId());
        return $qb->getQuery()->getResult();
    }

    // @codingStandardsIgnoreStart

    public function create(Block $block)
    {
        $this->_em->persist($block);
    }

    public function update(Block $block)
    {
        $this->_em->persist($block);
    }

    public function delete(Block $block)
    {
        $this->_em->remove($block);
    }

    public function commit()
    {
        $this->_em->flush();
    }

    // @codingStandardsIgnoreEnd
}
