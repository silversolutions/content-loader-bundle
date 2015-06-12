<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for policies
 */
class Policy extends AbstractValueObjectLoader
{
    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/roles/*/policies/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $struct = $this->repository->getRoleService()->newPolicyCreateStruct('', '');
        $this->fillValueObject($struct, $data);

        if (isset($data['limitations'])) {
            foreach ($data['limitations'] as $limitation) {
                $struct->addLimitation($limitation);
            }
        }

        return $struct;
    }
}