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
    protected $em;

    /**
     * @var int
     */
    protected $recursionDepth = 0;

    /**
     * @var int
     */
    protected $maxRecursionDepth = 0;

    /**
     * EntitySerializer constructor
     *
     * @param EntityManager $em Doctrine2 entity manager
     */
    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     * Get Doctrine 2 entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Set Doctrine 2 entity manager
     *
     * @param EntityManager $em Doctrine 2 entity manager
     *
     * @return $this
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * This function serialize entity
     *
     * @param mixed $entity Entity to serialize
     *
     * @return array
     * @SuppressWarnings("complexity")
     */
    protected function serializeEntity($entity)
    {
        $className = get_class($entity);
        $metadata  = $this->em->getClassMetadata($className);

        $data = [];

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
                if (null !== $data[$key]) {
                    $data[$key] = $this->serializeEntity($data[$key]);
                }
            } elseif ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE) {
                if (null !== $metadata->reflFields[$field]->getValue($entity)) {
                    if ($this->recursionDepth < $this->maxRecursionDepth) {
                        $this->recursionDepth++;
                        $data[$key] = $this->serializeEntity(
                            $metadata->reflFields[$field]
                                ->getValue($entity)
                        );
                        $this->recursionDepth--;
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
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Serialize an entity or array of entities to an array
     *
     * @param mixed|array $entity Entity to serialize to array
     *
     * @return array
     */
    public function toArray($entity)
    {
        if (is_array($entity)) {
            $arrayEntities = [];
            foreach ($entity as $ent) {
                $arrayEntities[] = $this->serializeEntity($ent);
            }
            return $arrayEntities;
        }
        return $this->serializeEntity($entity);
    }


    /**
     * Convert an entity or array of entities to a JSON object
     *
     * @param mixed|array $entity Entity to serialize to json
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
     * @param mixed $entity Entity object
     *
     * @throws Exception
     * This will suppress UnusedLocalVariable warnings in this method
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    public function toXml($entity)
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * Set the maximum recursion depth
     *
     * @param int $maxRecursionDepth Maximal depth
     *
     * @return  void
     */
    public function setMaxRecursionDepth($maxRecursionDepth)
    {
        $this->maxRecursionDepth = $maxRecursionDepth;
    }

    /**
     * Get the maximum recursion depth
     *
     * @return  int
     */
    public function getMaxRecursionDepth()
    {
        return $this->maxRecursionDepth;
    }

}
