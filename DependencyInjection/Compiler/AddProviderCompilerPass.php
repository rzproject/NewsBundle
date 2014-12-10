<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $collections = $container->getParameter('rz_news.provider.collections');

        foreach ($collections as $name => $settings) {
            $templates = array();

            foreach ($settings['templates'] as $template => $value) {
                $templates[$template] = $value;
            }
            $pool->addMethodCall('addCollection', array($name, $settings['provider'], $settings['default_template'], $templates));

            if($container->hasDefinition($settings['provider'])) {
                $provider =$container->getDefinition($settings['provider']);
                $provider->addMethodCall('setTemplates', array($templates));
                $provider->addMethodCall('setPostManager', array($id, new Reference('sonata.news.manager.post')));
            }
        }
    }
}
