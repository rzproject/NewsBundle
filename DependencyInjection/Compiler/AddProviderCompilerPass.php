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

        #set slugify service
        $serviceId = $container->getParameter('rz.news.slugify_service');

        ########################
        # Post Provider
        ########################
        $pool = $container->getDefinition('rz.news.post.pool');
        $pool->addMethodCall('setSlugify', array(new Reference($serviceId)));

        foreach ($container->findTaggedServiceIds('rz.news.post.provider') as $id => $attributes) {
            $pool->addMethodCall('addProvider', array($id, new Reference($id)));
        }

        $collections = $container->getParameter('rz.news.post.provider.collections');

        foreach ($collections as $name => $settings) {
            $pool->addMethodCall('addCollection', array($name, $settings['provider'], $settings['settings']));
            if ($container->hasDefinition($settings['provider'])) {
                $provider =$container->getDefinition($settings['provider']);
                $provider->addMethodCall('setPostManager', array(new Reference('sonata.news.manager.post')));
                $provider->addMethodCall('setCategoryManager', array(new Reference('sonata.classification.manager.category')));
                $provider->addMethodCall('setDefaultSettings', array($container->getParameter('rz.news.settings.post')));
                $provider->addMethodCall('setSlugify', array(new Reference($serviceId)));
            }
        }

        ########################
        # Post Sets Provider
        ########################
        $postSetsPool = $container->getDefinition('rz.news.post_sets.pool');
        $postSetsPool->addMethodCall('setSlugify', array(new Reference($serviceId)));
        foreach ($container->findTaggedServiceIds('rz.news.post_sets.provider') as $id => $attributes) {
            $postSetsPool->addMethodCall('addProvider', array($id, new Reference($id)));
        }

        $postSetsHasPostsPool = $container->getDefinition('rz.news.post_sets_has_posts.pool');
        $postSetsHasPostsPool->addMethodCall('setSlugify', array(new Reference($serviceId)));
        foreach ($container->findTaggedServiceIds('rz.news.post_sets_has_posts.provider') as $id => $attributes) {
            $postSetsHasPostsPool->addMethodCall('addProvider', array($id, new Reference($id)));
        }

        $collections = $container->getParameter('rz.news.post_sets.provider.collections');

        foreach ($collections as $name => $settings) {
            if ($settings['post_sets']['provider']) {
                $postSetsPool->addMethodCall('addCollection', array($name, $settings['post_sets']['provider'], array()));
                if ($container->hasDefinition($settings['post_sets']['provider'])) {
                    $provider =$container->getDefinition($settings['post_sets']['provider']);
//                    $provider->addMethodCall('setDefaultSettings', array($container->getParameter('rz.news.settings.post_sets')));
                    $provider->addMethodCall('setCategoryManager', array(new Reference('sonata.classification.manager.category')));
                }
            }

            if ($settings['post_sets_has_posts']['provider']) {
                $postSetsHasPostsPool->addMethodCall('addCollection', array($name, $settings['post_sets_has_posts']['provider'], $settings['post_sets_has_posts']['settings']));
                if ($container->hasDefinition($settings['post_sets_has_posts']['provider'])) {
                    $provider =$container->getDefinition($settings['post_sets_has_posts']['provider']);
                    $provider->addMethodCall('setCategoryManager', array(new Reference('sonata.classification.manager.category')));
                    $provider->addMethodCall('setDefaultSettings', array($container->getParameter('rz.news.settings.post_sets_has_posts')));
                }
            }
        }
    }
}
