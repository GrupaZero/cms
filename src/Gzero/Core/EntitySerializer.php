<?php namespace Gzero\Core;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Exception;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class EntitySerializer
 *
 * @package     Gzero
 * @author      Boris Guéry <guery.b@gmail.com> Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright   Copyright (c) 2014, Adrian Skierniewski
 */
class EntitySerializer {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * @var int
     */
    protected $_recursionDepth = 0;

    /**
     * @var int
     */
    protected $_maxRecursionDepth = 0;

    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;

        return $this;
    }

    protected function _serializeEntity($entity)
    {
        $className = get_class($entity);
        $metadata  = $this->_em->getClassMetadata($className);

        $data = array();

        foreach ($metadata->fieldMappings as $field => $mapping) {
            $value = $metadata->reflFields[$field]->getValue($entity);
            $field = Inflector::tableize($field);
            if ($value instanceof \DateTime) {
                // We cast DateTime to array to keep consistency with array result
                $data[$field] = (array) $value;
            } elseif (is_object($value)) {
                $data[$field] = (string) $value;
            } else {
                $data[$field] = $value;
            }
        }

        foreach ($metadata->associationMappings as $field => $mapping) {
            $key = Inflector::tableize($field);
            if ($mapping['isCascadeDetach']) {
                $data[$key] = $metadata->reflFields[$field]->getValue($entity);
                if (NULL !== $data[$key]) {
                    $data[$key] = $this->_serializeEntity($data[$key]);
                }
            } elseif ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE) {
                if (NULL !== $metadata->reflFields[$field]->getValue($entity)) {
                    if ($this->_recursionDepth < $this->_maxRecursionDepth) {
                        $this->_recursionDepth++;
                        $data[$key] = $this->_serializeEntity(
                            $metadata->reflFields[$field]
                                ->getValue($entity)
                        );
                        $this->_recursionDepth--;
                    } else {
                        $data[$key] = $this->getEntityManager()
                            ->getUnitOfWork()
                            ->getEntityIdentifier(
                                $metadata->reflFields[$field]
                                    ->getValue($entity)
                            );
                    }
                } else {
                    // In some case the relationship may not exist, but we want
                    // to know about it
                    $data[$key] = NULL;
                }
            }
        }

        return $data;
    }

    /**
     * Serialize an entity or array of entities to an array
     *
     * @param The entity|array $entity
     *
     * @return array
     */
    public function toArray($entity)
    {
        if (is_array($entity)) {
            $arrayEntities = [];
            foreach ($entity as $ent) {
                $arrayEntities[] = $this->_serializeEntity($ent);
            }
            return $arrayEntities;
        }
        return $this->_serializeEntity($entity);
    }


    /**
     * Convert an entity or array of entities to a JSON object
     *
     * @param The entity|array $entity
     *
     * @return string
     */
    public function toJson($entity)
    {
        return json_encode($this->toArray($entity));
    }

    /**
     * Convert an entity to XML representation
     *
     * @param The entity $entity
     *
     * @throws Exception
     */
    public function toXml($entity)
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * Set the maximum recursion depth
     *
     * @param   int $maxRecursionDepth
     *
     * @return  void
     */
    public function setMaxRecursionDepth($maxRecursionDepth)
    {
        $this->_maxRecursionDepth = $maxRecursionDepth;
    }

    /**
     * Get the maximum recursion depth
     *
     * @return  int
     */
    public function getMaxRecursionDepth()
    {
        return $this->_maxRecursionDepth;
    }

}
