<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Doctrine2TestCase
 *
 * @package    tests\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

class Doctrine2TestCase extends \PHPUnit_Framework_TestCase {

    protected $dbParams = [
        'driver' => 'pdo_sqlite',
        'memory' => TRUE
    ];
    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $paths     = array(__DIR__ . "/../src/Gzero/Entity");
        $isDevMode = TRUE;



        $config              = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $this->em = EntityManager::create($this->dbParams, $config);
        // Build the schema for sqlite
        $this->generateSchema();

        parent::setUp();
    }

    protected function generateSchema()
    {
        // Get the metadatas of entities to create the schema.
        $metadatas = $this->getMetadatas();

        if (!empty($metadatas)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->em);
            $tool->updateSchema($metadatas);
        } else {
            throw new \Doctrine\DBAL\Schema\SchemaException('No Metadata Classes to process.');
        }
    }

    /**
     * Overwrite this method to get specific metadatas.
     *
     * @return Array
     */
    protected function getMetadatas()
    {
        return $this->em->getMetadataFactory()->getAllMetadata();
    }

}
