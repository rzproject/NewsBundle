<?php

namespace Rz\NewsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('rz_news');
        $this->addManagerSection($node);
        $this->addModelSection($node);
        $this->addAdminSection($node);
        $this->addPermalinkSection($node);
        $this->addNewsPageSection($node);
        $this->addProviderSection($node);
        $this->addSettingsSection($node);
        return $treeBuilder;
    }
    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addSettingsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('slugify_service')
                    ->info('You should use: sonata.core.slugify.cocur, but for BC we keep \'sonata.core.slugify.native\' as default')
                    ->defaultValue('sonata.core.slugify.cocur')
                ->end()
                ->scalarNode('enable_controller')->defaultValue(false)->end()
            ->end()
        ;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addManagerSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('manager_class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('orm')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('post')->defaultValue('Rz\\NewsBundle\\Entity\\PostManager')->end()
                                ->scalarNode('post_has_category')->defaultValue('Rz\\NewsBundle\\Entity\\PostHasCategoryManager')->end()
                                ->scalarNode('post_has_media')->defaultValue('Rz\\NewsBundle\\Entity\\PostHasMediaManager')->end()
                                ->scalarNode('related_articles')->defaultValue('Rz\\NewsBundle\\Entity\\RelatedArticlesManager')->end()
                                ->scalarNode('suggested_articles')->defaultValue('Rz\\NewsBundle\\Entity\\SuggestedArticlesManager')->end()
                                ->scalarNode('post_has_page')->defaultValue('Rz\\NewsBundle\\Entity\\PostHasPageManager')->end()
                                ->scalarNode('post_sets')->defaultValue('Rz\\NewsBundle\\Entity\\PostSetsManager')->end()
                                ->scalarNode('post_sets_has_posts')->defaultValue('Rz\\NewsBundle\\Entity\\PostSetsHasPostsManager')->end()
                            ->end()
                        ->end()
                        ->arrayNode('mongodb')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('post')->defaultValue('Rz\\NewsBundle\\Document\\PostManager')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addProviderSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                    ->arrayNode('post')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('default_context')->isRequired()->end()
                            ->scalarNode('default_collection')->isRequired()->end()
                            ->scalarNode('default_provider_collection')->isRequired()->end()
                            ->arrayNode('collections')
                                ->useAttributeAsKey('id')
                                ->isRequired()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('provider')->isRequired()->end()
                                        ->scalarNode('preferred_template')->defaultValue('RzNewsBundle:Block:block_post_default.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('post_sets')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('default_context')->isRequired()->end()
                            ->scalarNode('default_collection')->isRequired()->end()
                            ->scalarNode('default_provider_collection')->isRequired()->end()
                            ->arrayNode('post_lookup_settings')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('default_collection')->isRequired()->end()
                                    ->scalarNode('hide_collection')->isRequired()->end()
                                ->end()
                            ->end()
                            ->arrayNode('collections')
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->children()
                                         ->arrayNode('post_sets')
                                             ->addDefaultsIfNotSet()
                                             ->children()
                                                ->scalarNode('provider')->end()
                                                ->arrayNode('post_lookup_settings')
                                                    ->children()
                                                        ->scalarNode('collection')->end()
                                                        ->scalarNode('hide_collection')->end()
                                                    ->end()
                                                ->end()
                                             ->end()
                                         ->end()
                                          ->arrayNode('post_sets_has_posts')
                                             ->addDefaultsIfNotSet()
                                             ->children()
                                                ->scalarNode('provider')->end()
                                             ->end()
                                         ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('seo')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('default_provider')->defaultValue('rz.news.provider.seo.default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addPermalinkSection(ArrayNodeDefinition $node)
    {
         $node
            ->children()
                ->arrayNode('permalink')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_permalink')->isRequired()->end()
                        ->scalarNode('default_category_permalink')->isRequired()->end()
                        ->arrayNode('permalinks')
                            ->useAttributeAsKey('id')
                            ->isRequired()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('permalink')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

     /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addNewsPageSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('news_page')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('page_templates')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                    ->scalarNode('template_code')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('news_page_parent_slug')->cannotBeEmpty()->defaultValue('article')->end()
                        ->arrayNode('transformer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Entity\\Transformer')->end()
                            ->end()
                        ->end()
                       ->arrayNode('manager_class')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('orm')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('post_has_page')->defaultValue('Rz\\NewsBundle\\Entity\\PostHasPageManager')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin')
                            ->addDefaultsIfNotSet()
                            ->children()
                               ->arrayNode('post_has_page')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostHasPageAdmin')->end()
                                        ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                        ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('class')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('post_has_page')->defaultValue('AppBundle\\Entity\\News\\PostHasPage')->end()
                                ->scalarNode('page')->defaultValue('AppBundle\\Entity\\Page\\Page')->end()
                                ->scalarNode('site')->defaultValue('AppBundle\\Entity\\Page\\Site')->end()
                                ->scalarNode('block')->defaultValue('AppBundle\\Entity\\Page\\Block')->end()
                            ->end()
                        ->end()
                        ->arrayNode('page_services')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('default')->defaultValue('rz.news.page.service.default')->end()
                                ->scalarNode('category')->defaultValue('rz.news.page.service.category')->end()
                                ->scalarNode('post_canonical')->defaultValue('rz.news.page.service.post_canonical')->end()
                                ->scalarNode('category_canonical')->defaultValue('rz.news.page.service.category_canonical')->end()
                            ->end()
                        ->end()
                        ->scalarNode('post_block_service')->defaultValue('rz.news.block.post')->cannotBeEmpty()->end()
                            ->arrayNode('default_post_block_template')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->defaultValue('Default')->cannotBeEmpty()->end()
                                    ->scalarNode('template')->defaultValue('RzNewsBundle:Block:block_post_default.html.twig')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addAdminSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                       ->arrayNode('post_has_category')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostHasCategoryAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('RzNewsBundle:PostHasCategoryAdmin')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_has_media')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostHasMediaAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                            ->end()
                        ->end()
                        ->arrayNode('related_articles')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\RelatedArticlesAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                            ->end()
                        ->end()
                        ->arrayNode('suggested_articles')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\SuggestedArticlesAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_sets')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostSetsAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('RzNewsBundle:PostSetsAdmin')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_sets_has_posts')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostSetsHasPostsAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addModelSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('post')->defaultValue('AppBundle\\Entity\\News\\Post')->end()
                        ->scalarNode('post_has_category')->defaultValue('AppBundle\\Entity\\News\\PostHasCategory')->end()
                        ->scalarNode('post_has_media')->defaultValue('AppBundle\\Entity\\News\\PostHasMedia')->end()
                        ->scalarNode('related_articles')->defaultValue('AppBundle\\Entity\\News\\RelatedArticles')->end()
                        ->scalarNode('suggested_articles')->defaultValue('AppBundle\\Entity\\News\\SuggestedArticles')->end()
                        ->scalarNode('post_sets')->defaultValue('AppBundle\\Entity\\News\\PostSets')->end()
                        ->scalarNode('post_sets_has_posts')->defaultValue('AppBundle\\Entity\\News\\PostSetsHasPosts')->end()
                        ->scalarNode('category')->defaultValue('AppBundle\\Entity\\Classification\\Category')->end()
                        ->scalarNode('collection')->defaultValue('AppBundle\\Entity\\Classification\\Collection')->end()
                        ->scalarNode('media')->defaultValue('AppBundle\\Entity\\Media\\Media')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
