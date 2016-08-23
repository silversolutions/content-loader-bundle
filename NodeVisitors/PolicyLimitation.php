<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for policy limitations
 */
class PolicyLimitation extends AbstractValueObjectLoader
{
    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/roles/*/policies/*/limitations/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $limitation = null;

        switch ($data['identifier']) {
            case 'SiteAccess':
                $limitation = new SiteAccessLimitation();
                break;
            case 'Section':
                $limitation = new SectionLimitation();
                break;
            // @todo: add other limitations
        }

        if ($limitation) {
            $this->fillValueObject($limitation, $data);
        }

        return $limitation;
    }
}