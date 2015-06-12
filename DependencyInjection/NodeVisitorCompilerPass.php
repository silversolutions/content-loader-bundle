<?php

namespace Siso\Bundle\ContentLoaderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Symfony container compiler pass for node visitors.
 */
class NodeVisitorCompilerPass implements CompilerPassInterface
{
    const TAG = 'siso.content_loader.visitor';
    const SERVICE = 'siso.content_loader.visitor_collection';

    /**
     * Collects all services with the tag for operations and add them as
     * a dependency to the catalog data provider service.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE)) {
            return;
        }

        /** @var Definition $definition */
        $definition = $container->getDefinition(
            self::SERVICE
        );

        /** @var array $taggedServices */
        $taggedServices = $container->findTaggedServiceIds(
            self::TAG
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addVisitor',
                array(new Reference($id))
            );
        }
    }
}
