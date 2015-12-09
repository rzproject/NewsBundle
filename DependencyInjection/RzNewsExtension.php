<?php

namespace Rz\NewsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RzNewsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $this->configureManagerClass($config, $container);

        $loader->load('provider.xml');
        $this->configureProviders($container, $config);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    public function configureManagerClass($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.entity.manager.post.class',        $config['manager_class']['orm']['post']);
        $container->setParameter('rz.news.document.manager.post.class',        $config['manager_class']['mongodb']['post']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureProviders(ContainerBuilder $container, $config)
    {
        $pool = $container->getDefinition('rz.news.pool');
        $pool->replaceArgument(0, $config['default_collection']);

        //set default collection
        $container->setParameter('rz.news.default_collection', $config['default_collection']);
        $container->setParameter('rz.news.provider.collections', $config['collections']);
    }
}
