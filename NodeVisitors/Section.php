<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\SectionService;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Traits\ProgressAwareTrait;

/**
 * Loader for sections
 */
class Section extends AbstractValueObjectLoader
{
    use ProgressAwareTrait;
    /**
     * @var SectionService
     */
    private $sectionService;

    public function __construct(SectionService $sectionService) {
        $this->sectionService = $sectionService;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/sections/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $this->doProgress('Creating sections...');
        $section = false;
        try {
            $section = $this->sectionService->loadSectionByIdentifier($data['identifier']);
        } catch (\Exception $e) {

        }

        if (!$section instanceof \eZ\Publish\API\Repository\Values\Content\Section) {
            $sectionCreate = $this->sectionService->newSectionCreateStruct();
            $sectionCreate->name = $data['name'];
            $sectionCreate->identifier = $data['identifier'];
            $section = $this->sectionService->createSection($sectionCreate);
        }

    }
}