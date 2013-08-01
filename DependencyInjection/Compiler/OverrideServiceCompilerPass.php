<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //override Media Admin
        $definition = $container->getDefinition('sonata.news.admin.category');
        $definition->addMethodCall('setCategoryManager', array(new Reference('sonata.news.manager.category')));
    }
}
