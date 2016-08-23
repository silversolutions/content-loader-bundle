<?php

namespace Siso\Bundle\ContentLoaderBundle\Writer;

use eZ\Publish\API\Repository\Repository;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ContentLoaderInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\DatabaseSchemaCreatorInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\FixtureLoaderInterface;
use Siso\Bundle\ContentLoaderBundle\Traits\ProgressAwareTrait;
use Siso\Bundle\ContentLoaderBundle\Traits\YamlParserTrait;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Field;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Config\FileLocatorInterface;



class FixtureWriter
{
    use ProgressAwareTrait;
    use YamlParserTrait;

    private $fileLocator;
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     * @param FileLocatorInterface $fileLocator
     */
    function __construct(
        Repository $repository,
        FileLocatorInterface $fileLocator
    ) {
        $this->fileLocator = $fileLocator;
        $this->repository = $repository;

    }

    /**
     * @param $path
     * @param $location_id
     * @param $depth
     */
    public function saveToFile($path, $location_id, $depth)
    {
        // $path = $this->fileLocator->locate($path);
        $objectList = array();

        $this->repository->sudo(
            function () use (&$objectList, $location_id, $depth) {
                $this->getDocuments($objectList, $location_id, $depth);
            }
        );

        $dumper = new Dumper($objectList);
        $yaml = $dumper->dump(array('content' => $objectList), 6);

        file_put_contents($path, $yaml);
    }

    /**
     * @param $objectList
     * @param $locationId
     * @param $depth
     */
    private function getDocuments(&$objectList, $locationId, $depth)
    {
        $location = $this->repository->getLocationService()->loadLocation( $locationId );
        try {
            $objectList  = array_merge($objectList, $this->getData( $locationId ));
        } catch (\Exception $e) {
            echo 'Problem with location '.$locationId.': '.$e->getMessage()."\n";
        }

        if ($depth == 0) {
            return;
        }
        try {
            $childLocationList = $this->repository->getLocationService()->loadLocationChildren( $location, 0, 20 );
            foreach ( $childLocationList->locations as $childLocation )
            {
                $this->getDocuments( $objectList, $childLocation->id, $depth -1 );
            }
        } catch (\Exception $e) {
            echo 'Exception  get chilren of location '.$locationId.': '.$e->getMessage()."\n";
        }

    }

    /**
     * @param $locationId
     * @return array
     */
    private function getData($locationId)
    {
        $data = array();

        // $languages = $this->configResolver->getParameter('languages');

        $languages = array('ger-DE', 'eng-GB', 'eng-US');

        /** @var  $location Location */
        $location = $this->repository->getLocationService()->loadLocation($locationId);

        /** @var $content Content */
        $content = $this->repository->getContentService()->loadContent( $location->contentId );
        $fieldDefinitions =  $this->repository
            ->getContentTypeService()
            ->loadContentType($content->contentInfo->contentTypeId)
            ->fieldDefinitions;

        $contentTypeIdentifier = $this->repository
            ->getContentTypeService()
            ->loadContentType($content->contentInfo->contentTypeId)
            ->identifier;

        $id = 'location_'.$locationId;
        $fieldList = array();
        foreach ($fieldDefinitions as $fieldDefinition) {
           if ($fieldDefinition->fieldTypeIdentifier == 'ezpage') {
               continue;
           }
           foreach ($languages as $lang) {
                /** @var $field \eZ\Publish\API\Repository\Values\Content\Field */
                $field = $content->getField($fieldDefinition->identifier, $lang);
                if ($field instanceof Field) {
                    $fieldList[$fieldDefinition->identifier][$lang] =
                        $this->mapFieldValue ($fieldDefinition->fieldTypeIdentifier, (string) $field->value);
                    if (!$fieldDefinition->isTranslatable) {
                        break; // only one content allowed in one lang
                    }

                }
            }
        }

        $data[$id] = array (
            'content_type' => $contentTypeIdentifier,
            'priority' => $location->priority,
            'parent' => $this->getParentId($location->parentLocationId),
            'fields' => $fieldList

        );
        return $data;
    }

    /**
     * @param $locationId
     * @return string
     */
    private function getParentId($locationId)
    {
        if (in_array($locationId, array(2,43))) {
            return $locationId;
        }

        return 'location_'.$locationId;
    }

    /**
     * @param $fieldDef
     * @param $value
     * @return int
     */
    private function mapFieldValue($fieldDef, $value)
    {
        switch($fieldDef) {
            case 'checkbox': return (integer)$value;
        }
        if ( $fieldDef != 'ezxmltext') {
            return $value;
        }

        return $value;

    }
}