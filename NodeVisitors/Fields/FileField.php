<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use eZ\Publish\API\Repository\Repository;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * Loader for file containing fields
 */
class FileField extends AbstractFieldLoader
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @param Repository $repository
     * @param FileLocatorInterface $fileLocator
     */
    public function __construct(Repository $repository, FileLocatorInterface $fileLocator)
    {
        parent::__construct($repository);
        $this->fileLocator = $fileLocator;
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if (($this->getContentTypeIdentifier($node) !== 'ezimage') &&
            ($this->getContentTypeIdentifier($node) !== 'ezfile')) {
            return null;
        }

        try {
            $path = $this->fileLocator->locate($data);
        } catch (\Exception $e) {
            $data = null;
            return null;
        }

        return $path;
    }
}