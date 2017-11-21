<?php

namespace Siso\Bundle\ContentLoaderBundle\Loader;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\DatabaseSchemaCreatorInterface;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class DatabaseSchemaCreator implements DatabaseSchemaCreatorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var string
     */
    private $kernelRootDir;
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;
    /**
     * @var CacheClearerInterface
     */
    private $cacheClearer;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ConfigResolverInterface $configResolver
     * @param string $kernelRootDir
     */
    function __construct(
        EntityManagerInterface $entityManager,
        ConfigResolverInterface $configResolver,
        $kernelRootDir
    ) {
        $this->entityManager = $entityManager;
        $this->kernelRootDir = $kernelRootDir;
        $this->configResolver = $configResolver;
    }

    /**
     * @inheritdoc
     */
    public function createSchema()
    {
        $this->createEzPublishSchema();
        $this->createDoctrineSchema();
        $this->createAdditionalSchema();
        //$this->cacheClearer->clear('');
    }

    /**
     * Creates eZ Publish database schema
     * @throws \Exception
     */
    protected function createEzPublishSchema()
    {
        $this->executeSqlFile($this->kernelRootDir.'/../vendor/ezsystems/ezpublish-kernel/data/mysql/schema.sql');
        $this->executeSqlFile($this->kernelRootDir.'/../ezpublish_legacy/kernel/sql/common/cleandata.sql');
    }

    /**
     * Executes given sql file
     *
     * @param string $path
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    private function executeSqlFile($path)
    {
        if (!file_exists($path)) {
            throw new \Exception(sprintf('SQL file does not exist: %s', $path));
        }

        $databaseSchema = file_get_contents($path);
        $this->entityManager->getConnection()->exec($databaseSchema);
    }

    /**
     * Creates schema for doctrine entities
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function createDoctrineSchema()
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->entityManager);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    /**
     * Creates additional tables specified in a configuration
     *
     * @throws \Exception
     */
    protected function createAdditionalSchema()
    {
        if (!$this->configResolver->hasParameter('sql_schema', 'siso_eshop')) {
            return;
        }

        $sqlSchema = $this->configResolver->getParameter('sql_schema', 'siso_eshop');
        foreach ($sqlSchema as $sqlFile) {
            $this->executeSqlFile($sqlFile);
        }
    }
}
