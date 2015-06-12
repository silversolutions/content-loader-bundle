<?php

namespace Siso\Bundle\ContentLoaderBundle;

use Siso\Bundle\ContentLoaderBundle\DependencyInjection\NodeVisitorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SisoContentLoaderBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NodeVisitorCompilerPass());
    }

}
