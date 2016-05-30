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

        if (interface_exists('Sonata\PageBundle\Model\BlockInteractorInterface')) {
            $blocks = $container->getParameter('sonata_block.blocks');
            $blockService = $container->getParameter('rz.news.post_block_service');
            if(isset($blocks[$blockService]) && isset($blocks[$blockService]['templates'])) {
                $container->setParameter('rz.news.post_templates', $blocks[$blockService]['templates']);
            }
        }

        $postTemplates = $container->getParameter('rz.news.post_templates');

        foreach ($container->findTaggedServiceIds('rz.news.post.provider') as $id => $attributes) {
            $pool->addMethodCall('addProvider', array($id, new Reference($id)));
        }

        $collections = $container->getParameter('rz.news.post.provider.collections');

        $templates = [];
        foreach ($postTemplates as $item) {
            $templates[$item['template']] = $item['name'];
        }

        foreach ($collections as $name => $settings) {
            $pool->addMethodCall('addCollection', array($name, $settings['provider'], $settings['preferred_template']));
            if($container->hasDefinition($settings['provider'])) {
                $provider =$container->getDefinition($settings['provider']);
                $provider->addMethodCall('setPostManager', array(new Reference('sonata.news.manager.post')));
                $provider->addMethodCall('setTemplates', array($templates));
                $provider->addMethodCall('setIsControllerEnabled', array($container->getParameter('rz.news.enable_controller')));

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
            if($settings['post_sets']['provider']) {

                $loockupCollection = $container->getParameter('rz.news.post_sets.default_post_lookup_collection');
                $hideCollection = $container->getParameter('rz.news.post_sets.default_post_lookup_hide_collection');

                if(array_key_exists('collection', $settings['post_sets']['post_lookup_settings'])) {
                    $loockupCollection =$settings['post_sets']['post_lookup_settings']['collection'];
                }

                if(array_key_exists('hide_collection', $settings['post_sets']['post_lookup_settings'])) {
                    $hideCollection =$settings['post_sets']['post_lookup_settings']['hide_collection'];
                }

                $postSetsPool->addMethodCall('addCollection', array($name, $settings['post_sets']['provider'], $loockupCollection, $hideCollection));
            }

            if($settings['post_sets_has_posts']['provider']) {
                $postSetsHasPostsPool->addMethodCall('addCollection', array($name, $settings['post_sets_has_posts']['provider']));
            }
        }
    }
}
