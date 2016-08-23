<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ValueObjectCollectionInterface;

/**
 * Loader for content objects
 */
class Content extends AbstractContentLoader
{
    /**
     * eZ Publish default location id
     */
    const DEFAULT_LOCATION_ID = 2;
    /**
     * @var ContentService
     */
    private $contentService;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;
    /**
     * @var SectionService
     */
    private $sectionService;
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var ValueObjectCollectionInterface
     */
    private $objectCollection;

    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        ValueObjectCollectionInterface $objectCollection,
        SectionService $sectionService,
        SearchService $searchService
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->objectCollection = $objectCollection;
        $this->sectionService = $sectionService;
        $this->searchService = $searchService;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/content/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if(!is_array($data)) {
            return null;
        }

        if (isset($data['unique'])) {
            // check if content already exists
            if ($this->contentExists($data)) {
                return null;
            }
        }

        // create structure
        $contentStruct = $this->getContentCreateStruct($data);
        $locationStruct = $this->getLocationCreateStruct($data, self::DEFAULT_LOCATION_ID);

        // publish content object
        $draft = $this->contentService->createContent($contentStruct, array($locationStruct));
        $publishedContent = $this->contentService->publishVersion($draft->versionInfo);

        // Add location to the location list
        if (isset($publishedContent->contentInfo)) {
            $this->objectCollection->add('locations', $node->getName(), $publishedContent->contentInfo->mainLocationId);
        }

        if (isset($data['section'])) {
            $section = $this->sectionService->loadSectionByIdentifier($data['section']);

            $this->sectionService->assignSection($publishedContent->contentInfo, $section);
        }
        // Add content to content list
        if (isset($publishedContent->contentInfo)) {
            $this->objectCollection->add('content_items', $node->getName(), $publishedContent->id);
        }

        return $publishedContent;
    }

    /**
     * Checks if a update is required
     * @param $data
     * @return boolean
     */
    private function contentExists($data)
    {
        $query = new Query();

        /** @var \eZ\Publish\API\Repository\LocationService $locationService */
        $location = $this->locationService->loadLocation( $this->getContentDataParentLocationId($data, self::DEFAULT_LOCATION_ID));
        $criteria = array(
            new Criterion\Subtree($location->pathString),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\Field($data['unique']['field'], Criterion\Operator::EQ, $data['unique']['value']),
        );

        $query->criterion = new Criterion\LogicalAnd($criteria);

        $languageFilter = array();
        $languageFilter['useAlwaysAvailable'] = true;
        try {
            $searchResults = $this->searchService->findContent($query, $languageFilter);
        } catch (\Exception $e) {
            return false;
        }

        if ($searchResults->totalCount !== 0) {
            return true;
        }
        return false;
    }

    /**
     * Creates and prepares content create structure.
     *
     * @param array $data
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    private function getContentCreateStruct($data)
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($data['content_type']);

        $struct = $this->contentService->newContentCreateStruct($contentType, '');
        $this->fillValueObject($struct, $data, ['content_type']);

        return $struct;
    }

    /**
     * Creates and prepares location create structure.
     *
     * @param array $data
     * @param int $defaultLocationId
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    private function getLocationCreateStruct($data, $defaultLocationId)
    {
        $parentLocationId = $this->getContentDataParentLocationId($data, $defaultLocationId);
        $locationStruct = $this->locationService->newLocationCreateStruct($parentLocationId);
        $locationStruct->priority = isset($data['priority']) ? $data['priority'] : 0;

        return $locationStruct;
    }

    /**
     * Get parent location id for content data and list of locations.
     *
     * @param array $data
     * @param int $defaultLocationId
     * @return int
     */
    private function getContentDataParentLocationId($data, $defaultLocationId)
    {
        $locationId = $defaultLocationId;
        if (array_key_exists('parent', $data)) {
            if (is_integer($data['parent'])) {
                return $data['parent'];
            } else {
                $locations = $this->objectCollection->getList('locations', [$data['parent']]);
                if ($locations) {
                    $locationId = $locations[0];
                }
            }

        }

        return $locationId;
    }
}