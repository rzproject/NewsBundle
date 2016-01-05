<?php

namespace Rz\NewsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;

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
        $loader->load('orm.xml');
        $loader->load('admin.xml');
        $this->configureManagerClass($config, $container);
        $this->configureClass($config, $container);
        $this->configureController($config, $container);
        $this->configureTranslationDomain($config, $container);

        $loader->load('provider.xml');
        $this->configureProviders($container, $config);
        $this->registerDoctrineMapping($config);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureClass($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.admin.post_has_category.entity', $config['class']['post_has_category']);
        $container->setParameter('rz.news.admin.post_has_media.entity', $config['class']['post_has_media']);

        $container->setParameter('rz.news.post_has_category.entity', $config['class']['post_has_category']);
        $container->setParameter('rz.news.post_has_media.entity', $config['class']['post_has_media']);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    public function configureManagerClass($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.entity.manager.post.class',        $config['manager_class']['orm']['post']);
        $container->setParameter('rz.news.manager.post_has_category.class',     $config['manager_class']['orm']['post_has_category']);
        $container->setParameter('rz.news.manager.post_has_media.class',     $config['manager_class']['orm']['post_has_media']);

        $container->setParameter('rz.news.document.manager.post.class',        $config['manager_class']['mongodb']['post']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureAdminClass($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.admin.post_has_category.class', $config['admin']['post_has_category']['class']);
        $container->setParameter('rz.news.admin.post_has_media.class', $config['admin']['post_has_media']['class']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureTranslationDomain($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.admin.post_has_category.translation_domain', $config['admin']['post_has_category']['translation']);
        $container->setParameter('rz.news.admin.post_has_media.translation_domain', $config['admin']['post_has_media']['translation']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureController($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.admin.post_has_category.controller', $config['admin']['post_has_category']['controller']);
        $container->setParameter('rz.news.admin.post_has_media.controller', $config['admin']['post_has_media']['controller']);
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

    /**
     * @param array $config
     */
    public function registerDoctrineMapping(array $config)
    {
        foreach ($config['class'] as $type => $class) {
            if (!class_exists($class)) {
                return;
            }
        }

        $collector = DoctrineCollector::getInstance();

        if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {

            $collector->addAssociation($config['class']['post_has_category'], 'mapManyToOne', array(
                'fieldName' => 'post',
                'targetEntity' => $config['class']['post'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => 'postHasCategory',
                'joinColumns' => array(
                    array(
                        'name' => 'post_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['class']['post_has_category'], 'mapManyToOne', array(
                'fieldName' => 'category',
                'targetEntity' => $config['class']['category'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => NULL,
                'joinColumns' => array(
                    array(
                        'name' => 'category_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['class']['post'], 'mapOneToMany', array(
                'fieldName' => 'postHasCategory',
                'targetEntity' => $config['class']['post_has_category'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => 'post',
                'orphanRemoval' => true,
                'orderBy' => array(
                    'position' => 'ASC',
                ),
            ));



        }

        if (interface_exists('Sonata\MediaBundle\Model\MediaInterface')) {

            $collector->addAssociation($config['class']['post_has_media'], 'mapManyToOne', array(
                'fieldName' => 'post',
                'targetEntity' => $config['class']['post'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => 'postHasMedia',
                'joinColumns' => array(
                    array(
                        'name' => 'post_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['class']['post_has_media'], 'mapManyToOne', array(
                'fieldName' => 'media',
                'targetEntity' => $config['class']['media'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => NULL,
                'joinColumns' => array(
                    array(
                        'name' => 'media_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['class']['post'], 'mapOneToMany', array(
                'fieldName' => 'postHasMedia',
                'targetEntity' => $config['class']['post_has_media'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => 'post',
                'orphanRemoval' => true,
                'orderBy' => array(
                    'position' => 'ASC',
                ),
            ));
        }
    }
}
