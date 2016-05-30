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
        $this->configureAdminClass($config, $container);
        $this->configureController($config, $container);
        $this->configureTranslationDomain($config, $container);

        $loader->load('permalink.xml');
        $this->configurePermalinks($container, $config);

        $loader->load('post_provider.xml');
        $loader->load('post_sets_provider.xml');
        $loader->load('seo_provider.xml');
        $this->configureProviders($container, $config['providers']);

        $container->setParameter('rz.news.enable_controller',  $config['enable_controller']);
        $container->setParameter('rz.news.slugify_service',    $config['slugify_service']);

        if (interface_exists('Sonata\PageBundle\Model\PageInterface') && !$config['enable_controller']) {
            $loader->load('orm_page.xml');
            $loader->load('admin_page.xml');
            $loader->load('transformer.xml');
            $loader->load('news_page_twig.xml');
            $loader->load('news_page_block.xml');
            $loader->load('news_page_service.xml');
            $this->configureNewsPage($container, $config);
        }

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
        $postPool->replaceArgument(0, $config['post']['default_provider_collection']);

        $container->setParameter('rz.news.post.default_context',                        $config['post']['default_context']);
        $container->setParameter('rz.news.post.default_collection',                     $config['post']['default_collection']);
        $container->setParameter('rz.news.post.provider.default_provider_collection',   $config['post']['default_provider_collection']);
        $container->setParameter('rz.news.post.provider.collections',                   $config['post']['collections']);


        #Post Sets Provider
        $postSetsPool = $container->getDefinition('rz.news.post_sets.pool');
        $postSetsPool->replaceArgument(0, $config['post_sets']['default_provider_collection']);

        $postSetsPool = $container->getDefinition('rz.news.post_sets_has_posts.pool');
        $postSetsPool->replaceArgument(0, $config['post_sets']['default_provider_collection']);

        $container->setParameter('rz.news.post_sets.default_context',                       $config['post_sets']['default_context']);
        $container->setParameter('rz.news.post_sets.default_collection',                    $config['post_sets']['default_collection']);
        $container->setParameter('rz.news.post_sets.default_post_lookup_collection',        $config['post_sets']['post_lookup_settings']['default_collection']);
        $container->setParameter('rz.news.post_sets.default_post_lookup_hide_collection',   $config['post_sets']['post_lookup_settings']['hide_collection']);

        $container->setParameter('rz.news.post_sets.provider.default_provider_collection',  $config['post_sets']['default_provider_collection']);
        $container->setParameter('rz.news.post_sets.provider.collections',                  $config['post_sets']['collections']);

        # SEO Provider
        $container->setParameter('rz.news.default_seo_provider',                            $config['seo']['default_provider']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configurePermalinks(ContainerBuilder $container, $config)
    {
        //set default permalinks
        $container->setParameter('rz.news.permalink.default_permalink', $config['permalink']['permalinks'][$config['permalink']['default_permalink']]['permalink']);
        $container->setParameter('rz.news.permalink.default_category_permalink', $config['permalink']['permalinks'][$config['permalink']['default_category_permalink']]['permalink']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureNewsPage(ContainerBuilder $container, $config)
    {
        $container->setParameter('rz.news.news_page_parent_slug',   $config['news_page']['news_page_parent_slug']);
        $container->setParameter('rz.news.transformer.class',       $config['news_page']['transformer']['class']);

        #postHasPage Class
        $container->setParameter('rz.news.admin.post_has_page.entity', $config['news_page']['class']['post_has_page']);
        $container->setParameter('rz.news.post_has_page.entity',       $config['news_page']['class']['post_has_page']);

        $container->setParameter('rz.news.entity.manager.post_has_page.class',     $config['news_page']['manager_class']['orm']['post_has_page']);
        $container->setParameter('rz.news.admin.post_has_page.class',              $config['news_page']['admin']['post_has_page']['class']);
        $container->setParameter('rz.news.admin.post_has_page.translation_domain', $config['news_page']['admin']['post_has_page']['translation']);
        $container->setParameter('rz.news.admin.post_has_page.controller',         $config['news_page']['admin']['post_has_page']['controller']);


        $container->setParameter('rz.news.default_post_block_template', $config['news_page']['default_post_block_template']);
        $container->setParameter('rz.news.post_templates',              array($config['news_page']['default_post_block_template']));
        $container->setParameter('rz.news.post_block_service',          $config['news_page']['post_block_service']);
        $container->setParameter('rz.news.page.services',               $config['news_page']['page_services']);

        if(!$config['news_page']['page_templates']) {
            throw new \RuntimeException(sprintf('Please define a default `page_templates` value for the class `%s`', get_class($this)));
        }

        $pageTemplates = [];
        foreach($config['news_page']['page_templates'] as $key=>$value) {
            $pageTemplates[$value['template_code']] = $value['name'];
        }

        $container->setParameter('rz.news.page_templates',                         $pageTemplates);

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

        ######################
        # Related Articles
        ######################

        $collector->addAssociation($config['class']['related_articles'], 'mapManyToOne', array(
            'fieldName' => 'post',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => NULL,
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
            'mappedBy' => NULL,
            'inversedBy' => NULL,
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
            'mappedBy' => NULL,
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
            'mappedBy' => NULL,
            'inversedBy' => NULL,
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
        # Page
        ######################

        if (interface_exists('Sonata\PageBundle\Model\PageInterface')) {

            $collector->addAssociation($config['news_page']['class']['post_has_page'], 'mapManyToOne', array(
                'fieldName' => 'post',
                'targetEntity' => $config['class']['post'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => 'postHasPage',
                'joinColumns' => array(
                    array(
                        'name' => 'post_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['news_page']['class']['post_has_page'], 'mapManyToOne', array(
                'fieldName' => 'page',
                'targetEntity' => $config['news_page']['class']['page'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => NULL,
                'joinColumns' => array(
                    array(
                        'name' => 'page_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['news_page']['class']['post_has_page'], 'mapManyToOne', array(
                'fieldName' => 'block',
                'targetEntity' => $config['news_page']['class']['block'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => NULL,
                'joinColumns' => array(
                    array(
                        'name' => 'block_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['news_page']['class']['post_has_page'], 'mapManyToOne', array(
                'fieldName' => 'sharedBlock',
                'targetEntity' => $config['news_page']['class']['block'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => NULL,
                'inversedBy' => NULL,
                'joinColumns' => array(
                    array(
                        'name' => 'shared_block_id',
                        'referencedColumnName' => 'id',
                    ),
                ),
                'orphanRemoval' => false,
            ));

            $collector->addAssociation($config['class']['post'], 'mapOneToMany', array(
                'fieldName' => 'postHasPage',
                'targetEntity' => $config['news_page']['class']['post_has_page'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => 'post',
                'orphanRemoval' => true,
                'orderBy' => array(
                    'position' => 'ASC',
                ),
            ));

            $collector->addAssociation($config['class']['post'], 'mapManyToOne', array(
                'fieldName'     => 'site',
                'targetEntity'  => $config['news_page']['class']['site'],
                'cascade'       => array(
                    'persist',
                ),
                'mappedBy'      => null,
                'inversedBy'    => null,
                'joinColumns'   => array(
                    array(
                        'name'                 => 'site_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'CASCADE',
                    ),
                ),
                'orphanRemoval' => false,
            ));


            if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {

                $collector->addAssociation($config['news_page']['class']['post_has_page'], 'mapManyToOne', array(
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
            }
        }


        ######################
        # PostSets Has Post
        ######################

        $collector->addAssociation($config['class']['post_sets_has_posts'], 'mapManyToOne', array(
            'fieldName' => 'post',
            'targetEntity' => $config['class']['post'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => NULL,
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
            'mappedBy' => NULL,
            'inversedBy' => NULL,
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
