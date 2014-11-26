<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)


    {
        //override news admin post
        $definition = $container->getDefinition('sonata.news.admin.post');
        $definition->addMethodCall('setContextManager', array(new Reference('sonata.classification.manager.context')));
        $definition->addMethodCall('setCollectionManager', array(new Reference('sonata.classification.manager.collection')));
        $definition->addMethodCall('setPool', array(new Reference('rz_news.pool')));
        $this->fixTemplates($container, $definition, 'rz_news.configuration.post.templates');


        //override news admin comments
        $definition = $container->getDefinition('sonata.news.admin.comment');
        $this->fixTemplates($container, $definition, 'rz_news.configuration.comment.templates');

        //override blocks
        $definition = $container->getDefinition('sonata.news.block.recent_posts');
        $definition->setClass($container->getParameter('rz_news.block.recent_posts'));

        $definition = $container->getDefinition('sonata.news.block.recent_comments');
        $definition->setClass($container->getParameter('rz_news.block.recent_comments'));


        //replace permalink class
        $container->getDefinition('sonata.news.blog')
            ->replaceArgument(3, new Reference('rz_news.permalink.post'));
    }

    /**
     * @param  \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param  \Symfony\Component\DependencyInjection\Definition $definition
     * @param $templates
     *
     * @return void
     */
    public function fixTemplates(ContainerBuilder $container, Definition $definition, $templates)
    {
        $defaultTemplates = $container->getParameter('sonata.admin.configuration.templates');
        $definedTemplates = array_merge($defaultTemplates, $container->getParameter('rz_admin.configuration.templates'));
        $definedTemplates = array_merge($definedTemplates, $container->getParameter($templates));

        $methods = array();
        $pos = 0;

        //override all current sonata admin with the Rz Templates
        foreach ($definition->getMethodCalls() as $method) {
            if ($method[0] == 'setTemplates') {
                $definedTemplates = array_merge($definedTemplates, $method[1][0]);
                continue;
            }

            if ($method[0] == 'setTemplate') {
                $definedTemplates[$method[1][0]] = $method[1][1];
                continue;
            }

            $methods[$pos] = $method;
            $pos++;
        }

        $definition->setMethodCalls($methods);
        $definition->addMethodCall('setTemplates', array($definedTemplates));
    }
}
