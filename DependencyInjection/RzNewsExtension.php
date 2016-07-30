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
        $loader->load('validators.xml');
        $loader->load('serializer.xml');
        $this->configureSettings($config, $container);
        $this->configureManagerClass($config, $container);
        $this->configureClass($config, $container);
        $this->configureAdminClass($config, $container);
        $this->configureController($config, $container);
        $this->configureTranslationDomain($config, $container);

        $loader->load('post_provider.xml');
        $loader->load('post_sets_provider.xml');
        $loader->load('post_sets_has_posts_provider.xml');
        $this->configureProviders($container, $config);

        $this->registerDoctrineMapping($config);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureSettings($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.slugify_service',                      $config['slugify_service']);

        $container->setParameter('rz.news.settings.post',                        $config['settings']['post']);
        $container->setParameter('rz.news.post.default_context',                 $config['settings']['post']['default_context']);
        $container->setParameter('rz.news.post.default_collection',              $config['settings']['post']['default_collection']);

        $container->setParameter('rz.news.settings.post_sets',                   $config['settings']['post_sets']);
        $container->setParameter('rz.news.post_sets.default_context',            $config['settings']['post_sets']['default_context']);
        $container->setParameter('rz.news.post_sets.default_collection',         $config['settings']['post_sets']['default_collection']);

        $container->setParameter('rz.news.settings.post_sets_has_posts',         $config['settings']['post_sets_has_posts']);

        $container->setParameter('rz.news.post_has_category.category.default_context',   $config['settings']['post_has_category']['category']['default_context']);
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
        $container->setParameter('rz.news.admin.related_articles.entity', $config['class']['related_articles']);
        $container->setParameter('rz.news.admin.suggested_articles.entity', $config['class']['suggested_articles']);
        $container->setParameter('rz.news.admin.post_sets.entity', $config['class']['post_sets']);
        $container->setParameter('rz.news.admin.post_sets_has_posts.entity', $config['class']['post_sets_has_posts']);

        $container->setParameter('rz.news.post_has_category.entity', $config['class']['post_has_category']);
        $container->setParameter('rz.news.post_has_media.entity', $config['class']['post_has_media']);
        $container->setParameter('rz.news.related_articles.entity', $config['class']['related_articles']);
        $container->setParameter('rz.news.suggested_articles.entity', $config['class']['suggested_articles']);
        $container->setParameter('rz.news.post_sets.entity', $config['class']['post_sets']);
        $container->setParameter('rz.news.post_sets_has_posts.entity', $config['class']['post_sets_has_posts']);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    public function configureManagerClass($config, ContainerBuilder $container)
    {
        $container->setParameter('rz.news.entity.manager.post.class',                   $config['manager_class']['orm']['post']);
        $container->setParameter('rz.news.entity.manager.post_has_category.class',      $config['manager_class']['orm']['post_has_category']);
        $container->setParameter('rz.news.entity.manager.post_has_media.class',         $config['manager_class']['orm']['post_has_media']);
        $container->setParameter('rz.news.entity.manager.related_articles.class',       $config['manager_class']['orm']['related_articles']);
        $container->setParameter('rz.news.entity.manager.suggested_articles.class',     $config['manager_class']['orm']['suggested_articles']);
        $container->setParameter('rz.news.entity.manager.post_sets.class',              $config['manager_class']['orm']['post_sets']);
        $container->setParameter('rz.news.entity.manager.post_sets_has_posts.class',     $config['manager_class']['orm']['post_sets_has_posts']);

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
        $container->setParameter('rz.news.admin.related_articles.class', $config['admin']['related_articles']['class']);
        $container->setParameter('rz.news.admin.suggested_articles.class', $config['admin']['suggested_articles']['class']);
        $container->setParameter('rz.news.admin.post_sets.class', $config['admin']['post_sets']['class']);
        $container->setParameter('rz.news.admin.post_sets_has_posts.class', $config['admin']['post_sets_has_posts']['class']);
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
        $container->setParameter('rz.news.admin.related_articles.translation_domain', $config['admin']['related_articles']['translation']);
        $container->setParameter('rz.news.admin.suggested_articles.translation_domain', $config['admin']['suggested_articles']['translation']);
        $container->setParameter('rz.news.admin.post_sets.translation_domain', $config['admin']['post_sets']['translation']);
        $container->setParameter('rz.news.admin.post_sets_has_posts.translation_domain', $config['admin']['post_sets_has_posts']['translation']);
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
        $container->setParameter('rz.news.admin.related_articles.controller', $config['admin']['related_articles']['controller']);
        $container->setParameter('rz.news.admin.suggested_articles.controller', $config['admin']['suggested_articles']['controller']);
        $container->setParameter('rz.news.admin.post_sets.controller', $config['admin']['post_sets']['controller']);
        $container->setParameter('rz.news.admin.post_sets_has_posts.controller', $config['admin']['post_sets_has_posts']['controller']);
    }


    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureProviders(ContainerBuilder $container, $config)
    {
        #Post Provider
        $postPool = $container->getDefinition('rz.news.post.pool');
        $postPool->replaceArgument(0, $config['settings']['post']['default_collection']);

        $container->setParameter('rz.news.post.provider.collections',                   $config['providers']['post']['collections']);


        #Post Sets Provider
        $postSetsPool = $container->getDefinition('rz.news.post_sets.pool');
        $postSetsPool->replaceArgument(0, $config['settings']['post_sets']['default_collection']);

        $postSetsPool = $container->getDefinition('rz.news.post_sets_has_posts.pool');
        $postSetsPool->replaceArgument(0, $config['settings']['post_sets']['default_collection']);

        $container->setParameter('rz.news.post_sets.provider.collections',             $config['providers']['post_sets']['collections']);
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
                'mappedBy' => null,
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
                'mappedBy' => null,
                'inversedBy' => null,
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
                'mappedBy' => null,
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
                'mappedBy' => null,
                'inversedBy' => null,
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

        ######################
        # Related Articles
        ######################

        $collector->addAssociation($config['class']['related_articles'], 'mapManyToOne', array(
            'fieldName' => 'post',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => 'relatedArticles',
            'joinColumns' => array(
                array(
                    'name' => 'post_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['related_articles'], 'mapManyToOne', array(
            'fieldName' => 'relatedArticle',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => array(
                array(
                    'name' => 'related_article_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['post'], 'mapOneToMany', array(
            'fieldName' => 'relatedArticles',
            'targetEntity' => $config['class']['related_articles'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => 'post',
            'orphanRemoval' => true,
            'orderBy' => array(
                'position' => 'ASC',
            ),
        ));

        ######################
        # Suggested Articles
        ######################

        $collector->addAssociation($config['class']['suggested_articles'], 'mapManyToOne', array(
            'fieldName' => 'post',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => 'suggestedArticles',
            'joinColumns' => array(
                array(
                    'name' => 'post_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['suggested_articles'], 'mapManyToOne', array(
            'fieldName' => 'suggestedArticle',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => array(
                array(
                    'name' => 'suggested_article_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['post'], 'mapOneToMany', array(
            'fieldName' => 'suggestedArticles',
            'targetEntity' => $config['class']['suggested_articles'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => 'post',
            'orphanRemoval' => true,
            'orderBy' => array(
                'position' => 'ASC',
            ),
        ));

        ######################
        # PostSets Has Post
        ######################

        $collector->addAssociation($config['class']['post_sets_has_posts'], 'mapManyToOne', array(
            'fieldName' => 'post',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => 'postSetsHasPosts',
            'joinColumns' => array(
                array(
                    'name' => 'post_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));


        $collector->addAssociation($config['class']['post_sets_has_posts'], 'mapManyToOne', array(
            'fieldName' => 'postSets',
            'targetEntity' => $config['class']['post_sets'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => array(
                array(
                    'name' => 'post_sets_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['post_sets'], 'mapOneToMany', array(
            'fieldName' => 'postSetsHasPosts',
            'targetEntity' => $config['class']['post_sets_has_posts'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => 'postSets',
            'orphanRemoval' => true,
            'orderBy' => array(
                'position' => 'ASC',
            ),
        ));

        if (interface_exists('Sonata\ClassificationBundle\Model\CollectionInterface')) {
            $collector->addAssociation($config['class']['post_sets'], 'mapManyToOne', array(
                'fieldName'     => 'collection',
                'targetEntity'  => $config['class']['collection'],
                'cascade'       => array(
                    'persist',
                ),
                'mappedBy'      => null,
                'inversedBy'    => null,
                'joinColumns'   => array(
                    array(
                        'name'                 => 'collection_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'SET NULL',
                    ),
                ),
                'orphanRemoval' => false,
            ));
        }
    }
}
