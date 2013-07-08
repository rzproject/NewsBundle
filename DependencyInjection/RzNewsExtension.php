<?php

/*
 * This file is part of the RzNewsBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RzNewsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('admin_orm.xml');

        $config = $this->addDefaults($config);
        $this->configureAdminClass($config, $container);
        $this->configureClass($config, $container);
        $this->configureClassManager($config, $container);

        $this->configureTranslationDomain($config, $container);
        $this->configureController($config, $container);
        $this->configureRzTemplates($config, $container);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    public function addDefaults(array $config)
    {
        if ('orm' === $config['manager_type']) {
            $modelType = 'Entity';
        } elseif ('mongodb' === $config['manager_type']) {
            $modelType = 'Document';
        }

        $defaultConfig['class']['post']  = sprintf('Application\\Sonata\\NewsBundle\\%s\\Post', $modelType);
        $defaultConfig['class']['category'] = sprintf('Application\\Sonata\\NewsBundle\\%s\\Category', $modelType);
        $defaultConfig['class']['comment'] = sprintf('Application\\Sonata\\NewsBundle\\%s\\Comment', $modelType);
        $defaultConfig['class']['tag'] = sprintf('Application\\Sonata\\NewsBundle\\%s\\Tag', $modelType);

        return array_replace_recursive($defaultConfig, $config);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureClass($config, ContainerBuilder $container)
    {
        if ('orm' === $config['manager_type']) {
            $modelType = 'entity';
        } elseif ('mongodb' === $config['manager_type']) {
            $modelType = 'document';
        }

        $container->setParameter(sprintf('sonata.news.admin.post.%s', $modelType), $config['class']['post']);
        $container->setParameter(sprintf('sonata.news.admin.tag.%s', $modelType), $config['class']['tag']);
        $container->setParameter(sprintf('sonata.news.admin.comment.%s', $modelType), $config['class']['comment']);
        $container->setParameter(sprintf('sonata.news.admin.category.%s', $modelType), $config['class']['category']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureClassManager($config, ContainerBuilder $container)
    {
        // manager configuration
        $container->setParameter('sonata.news.manager.post.class',     $config['class_manager']['post']);
        $container->setParameter('sonata.news.manager.tag.class',      $config['class_manager']['tag']);
        $container->setParameter('sonata.news.manager.comment.class',  $config['class_manager']['comment']);
        $container->setParameter('sonata.news.manager.category.class', $config['class_manager']['category']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureAdminClass($config, ContainerBuilder $container)
    {
        $container->setParameter('sonata.news.admin.post.class', $config['admin']['post']['class']);
        $container->setParameter('sonata.news.admin.tag.class', $config['admin']['tag']['class']);
        $container->setParameter('sonata.news.admin.comment.class', $config['admin']['comment']['class']);
        $container->setParameter('sonata.news.admin.category.class', $config['admin']['category']['class']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureTranslationDomain($config, ContainerBuilder $container)
    {
        $container->setParameter('sonata.news.admin.post.translation_domain', $config['admin']['post']['translation']);
        $container->setParameter('sonata.news.admin.tag.translation_domain', $config['admin']['tag']['translation']);
        $container->setParameter('sonata.news.admin.comment.translation_domain', $config['admin']['comment']['translation']);
        $container->setParameter('sonata.news.admin.category.translation_domain', $config['admin']['category']['translation']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureController($config, ContainerBuilder $container)
    {
        $container->setParameter('sonata.news.admin.post.controller', $config['admin']['post']['controller']);
        $container->setParameter('sonata.news.admin.tag.controller', $config['admin']['tag']['controller']);
        $container->setParameter('sonata.news.admin.comment.controller', $config['admin']['comment']['controller']);
        $container->setParameter('sonata.news.admin.category.controller', $config['admin']['category']['controller']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureRzTemplates($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_news.configuration.post.templates', $config['admin']['post']['templates']);
        $container->setParameter('rz_news.configuration.tag.templates', $config['admin']['tag']['templates']);
        $container->setParameter('rz_news.configuration.comment.templates', $config['admin']['comment']['templates']);
        $container->setParameter('rz_news.configuration.category.templates', $config['admin']['category']['templates']);
    }
}
