<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\ValueObject\Diff;

/**
 * Loader for content types
 */
class ContentType extends AbstractValueObjectLoader
{
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    private $defaultGroup;
    /**
     * @var Diff
     */
    private $diff;

    /**
     * @param ContentTypeService $contentTypeService
     * @param Diff $diff
     */
    public function __construct(ContentTypeService $contentTypeService, Diff $diff)
    {
        $this->contentTypeService = $contentTypeService;
        $this->defaultGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $this->diff = $diff;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/content_types/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $contentTypeIdentifier = $data['identifier'];

        if ($this->isExistingContentType($contentTypeIdentifier)) {
            $draft = $this->getUpdatedDraft($data);
        } else {
            $draft = $this->getNewDraft($data);
        }

        $this->contentTypeService->publishContentTypeDraft($draft);
    }

    /**
     * Returns true if content type with the given identifier exists
     *
     * @param string $contentTypeIdentifier
     * @return bool
     */
    private function isExistingContentType($contentTypeIdentifier)
    {
        try {
            $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);

            return true;
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * Update content type definition and returns a draft
     *
     * @param array $data
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    private function getUpdatedDraft(array &$data)
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($data['identifier']);
        $draft = $this->contentTypeService->createContentTypeDraft($contentType);

        // Update content type
        $struct = $this->contentTypeService->newContentTypeUpdateStruct($data);
        $this->fillValueObject($struct, $data);

        $diff = $this->diff->diff($contentType->fieldDefinitions, $data['field_definitions'], 'identifier');
        // @todo: introduce field updates (now only remove and add are supported)

        $this->updateDraftFields($draft, $diff, $data);
        $this->contentTypeService->updateContentTypeDraft($draft, $struct);

        return $draft;
    }

    /**
     * Update draft fields with diff data
     *
     * @param ContentTypeDraft $draft
     * @param array $diff
     * @param array $data
     */
    private function updateDraftFields(ContentTypeDraft $draft, array $diff, array &$data)
    {
        // Remove fields which are missing in the new definition
        foreach ($draft->fieldDefinitions as $fieldDefinition) {
            if (in_array($fieldDefinition->identifier, $diff['remove'])) {
                $this->contentTypeService->removeFieldDefinition($draft, $fieldDefinition);
            }
        }

        // Add fields which are missing in the old content type
        $fieldStructs = $data['field_definitions'];//$this->getFieldDefinitionCreateStructs($data);
        foreach ($fieldStructs as $fieldStruct) {
            if (in_array($fieldStruct->identifier, $diff['add'])) {
                $this->contentTypeService->addFieldDefinition($draft, $fieldStruct);
            }
        }
    }

    /**
     * Create new content type draft
     *
     * @param array $data
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    private function getNewDraft(array &$data)
    {
        $struct = $this->contentTypeService->newContentTypeCreateStruct($data['identifier']);
        $this->fillValueObject($struct, $data);
        $struct->mainLanguageCode = $this->getContentDataMainLanguage($data);

        return $this->contentTypeService->createContentType($struct, [$this->defaultGroup]);
    }

    /**
     * Get main language from content type data
     *
     * @param array $data
     * @return string
     */
    private function getContentDataMainLanguage(&$data)
    {
        // Get first language of the first field
        $names = array_keys($data['names']);

        return $names[0];
    }
}