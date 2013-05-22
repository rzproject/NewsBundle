<?php

namespace Rz\NewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //$rz_definition = $container->getDefinition('rz_user.admin.user');
        $definition = $container->getDefinition('sonata.news.admin.post');
        $definition->setClass($container->getParameter('rz_news.admin.post.class'));

        $definition = $container->getDefinition('sonata.news.admin.comment');
        $definition->setClass($container->getParameter('rz_news.admin.comment.class'));

        $definition = $container->getDefinition('sonata.news.admin.category');
        $definition->setClass($container->getParameter('rz_news.admin.category.class'));

        $definition = $container->getDefinition('sonata.news.admin.tag');
        $definition->setClass($container->getParameter('rz_news.admin.tag.class'));
    }
}
