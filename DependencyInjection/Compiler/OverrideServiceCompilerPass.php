<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        #set slugify service
        $serviceId = $container->getParameter('rz.news.slugify_service');

        #####################################
        ## Override Entity Manager
        #####################################
        $definition = $container->getDefinition('sonata.news.manager.post');
        $definition->setClass($container->getParameter('rz.news.entity.manager.post.class'));

        ########################################
        ## PostAdmin
        ########################################
        $definition = $container->getDefinition('sonata.news.admin.post');
        $definition->addMethodCall('setCollectionManager', array(new Reference('sonata.classification.manager.collection')));
        $definition->addMethodCall('setContextManager', array(new Reference('sonata.classification.manager.context')));
        $definition->addMethodCall('setCategoryManager', array(new Reference('sonata.classification.manager.category')));
        $definition->addMethodCall('setTagManager', array(new Reference('sonata.classification.manager.tag')));
        $definition->addMethodCall('setPool', array(new Reference('rz.news.post.pool')));
        $definition->addMethodCall('setSecurityTokenStorage', array(new Reference('security.token_storage')));
        #set slugify service
        $definition->addMethodCall('setSlugify', array(new Reference($serviceId)));
        #slugify context and collection
        $definition->addMethodCall('setDefaultContext', array($container->getParameter('rz.news.post.default_context')));
        $definition->addMethodCall('setDefaultCollection', array($container->getParameter('rz.news.post.default_collection')));
        $definition->addMethodCall('setSettings', array($container->getParameter('rz.news.settings.post')));



        ########################################
        ## PostSetsAdmin & PostSetsHasPostsAdmin
        ########################################
        $definition = $container->getDefinition('rz.news.admin.post_sets');
        $definition->addMethodCall('setPool', array(new Reference('rz.news.post_sets.pool')));
        $definition->addMethodCall('setChildPool', array(new Reference('rz.news.post_sets_has_posts.pool')));
        $definition->addMethodCall('setDefaultContext', array($container->getParameter('rz.news.post_sets.default_context')));
        $definition->addMethodCall('setDefaultCollection', array($container->getParameter('rz.news.post_sets.default_collection')));
        $definition->addMethodCall('setCollectionManager', array(new Reference('sonata.classification.manager.collection')));
        $definition->addMethodCall('setContextManager', array(new Reference('sonata.classification.manager.context')));
        $definition->addMethodCall('setSlugify', array(new Reference($serviceId)));
        $definition->addMethodCall('setSettings', array($container->getParameter('rz.news.settings.post_sets')));

        $definition = $container->getDefinition('rz.news.admin.post_sets_has_posts');
        $definition->addMethodCall('setPool', array(new Reference('rz.news.post_sets_has_posts.pool')));
        $definition->addMethodCall('setDefaultContext', array($container->getParameter('rz.news.post_sets.default_context')));
        $definition->addMethodCall('setDefaultCollection', array($container->getParameter('rz.news.post_sets.default_collection')));
        $definition->addMethodCall('setCollectionManager', array(new Reference('sonata.classification.manager.collection')));
        $definition->addMethodCall('setSettings', array($container->getParameter('rz.news.settings.post_sets_has_posts')));


        ########################################
        ## PostHasCategory
        ########################################
        $definition = $container->getDefinition('rz.news.admin.post_has_category');
        $definition->addMethodCall('setSlugify', array(new Reference($serviceId)));
        $definition->addMethodCall('setDefaultContext', array($container->getParameter('rz.news.post_has_category.category.default_context')));
    }
}
