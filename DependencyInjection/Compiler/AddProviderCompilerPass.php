<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->attachProviders($container);
    }



    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function attachProviders(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('rz_news.pool');
        foreach ($container->findTaggedServiceIds('rz_news.provider') as $id => $attributes) {
            $pool->addMethodCall('addProvider', array($id, new Reference($id)));
        }
    }
}
