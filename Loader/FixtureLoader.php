<?php

namespace Siso\Bundle\ContentLoaderBundle\Loader;

use eZ\Publish\API\Repository\Repository;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ContentLoaderInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\DatabaseSchemaCreatorInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\FixtureLoaderInterface;
use Siso\Bundle\ContentLoaderBundle\Traits\ProgressAwareTrait;
use Siso\Bundle\ContentLoaderBundle\Traits\YamlParserTrait;
use Symfony\Component\Config\FileLocatorInterface;

class FixtureLoader implements FixtureLoaderInterface
{
    use ProgressAwareTrait;
    use YamlParserTrait;

    private $fileLocator;
    /**
     * @var Repository
     */
    private $repository;
    /**
     * @var ContentLoaderInterface
     */
    private $loader;
    /**
     * @var DatabaseSchemaCreatorInterface
     */
    private $databaseSchemaCreator;

    /**
     * @param Repository $repository
     * @param ContentLoaderInterface $loader
     * @param DatabaseSchemaCreatorInterface $databaseSchemaCreator
     * @param FileLocatorInterface $fileLocator
     */
    function __construct(
        Repository $repository,
        ContentLoaderInterface $loader,
        DatabaseSchemaCreatorInterface $databaseSchemaCreator,
        FileLocatorInterface $fileLocator
    ) {
        $this->fileLocator = $fileLocator;
        $this->repository = $repository;
        $this->loader = $loader;
        $this->databaseSchemaCreator = $databaseSchemaCreator;
    }

    /**
     * @inheritdoc
     */
    public function loadFromFile($path, $remove)
    {
        $path = $this->fileLocator->locate($path);
        $data = $this->loadYamlFile($path);
        $this->load($data, $remove);
    }

    /**
     * @inheritdoc
     */
    public function load($data, $remove)
    {
        if ($remove) {

            $this->doProgress('Creating database schema...');
            $this->databaseSchemaCreator->createSchema();
        }


        $this->doProgress('Loading fixtures...');
        // Always use repositoty sudo to get access for content creation
        $this->repository->sudo(
            function () use ($data) {
                $this->loader->load($data);
            }
        );

    }
}