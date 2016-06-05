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
                ->arrayNode('settings')
                    ->children()
                        ->arrayNode('post')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('media')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('default_context')->defaultNull()->end()
                                        ->scalarNode('hide_context')->defaultValue(false)->end()
                                        ->scalarNode('default_category')->defaultNull()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('related_articles')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('enabled')->defaultValue(false)->end()
                                        ->scalarNode('default_collection')->defaultNull()->end()
                                        ->scalarNode('hide_collection')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                                ->arrayNode('suggested_articles')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('enabled')->defaultValue(false)->end()
                                        ->scalarNode('default_collection')->defaultNull()->end()
                                        ->scalarNode('hide_collection')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                                ->arrayNode('post_has_media')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('enabled')->defaultValue(false)->end()
                                        ->scalarNode('default_context')->defaultNull()->end()
                                        ->scalarNode('hide_context')->defaultValue(false)->end()
                                        ->scalarNode('default_category')->defaultNull()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('post_has_category')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('default_context')->defaultNull()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('tags')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('default_context')->defaultNull()->end()
                                        ->scalarNode('hide_context')->defaultValue(true)->end()
                                    ->end()
                                ->end()  #--end tags
                            ->end()  #--end post children
                        ->end() #--end post
                        ->arrayNode('post_sets_has_posts')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('post')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('default_collection')->cannotBeEmpty()->end()
                                        ->scalarNode('hide_collection')->cannotBeEmpty()->defaultValue(true)->end()
                                    ->end()
                                ->end()  #--end default
                            ->end()  #--end post children
                        ->end() #--end post_sets
                    ->end()
                ->end()
            ->end();
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
                            ->arrayNode('collections')
                                ->useAttributeAsKey('id')
                                ->isRequired()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('provider')->isRequired()->end()
                                        ->arrayNode('settings')
                                            ->useAttributeAsKey('id')
                                            ->prototype('array')
                                                ->children()
                                                    ->arrayNode('params')
                                                        ->prototype('array')
                                                            ->children()
                                                                ->scalarNode('key')->end()
                                                                ->scalarNode('value')->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end() #--> end settings
                                    ->end()
                                ->end()
                            ->end()  #--> end collections
                        ->end()
                    ->end()
                    ->arrayNode('post_sets')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('default_context')->isRequired()->end()
                            ->scalarNode('default_collection')->isRequired()->end()
                            ->arrayNode('collections')
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->children()
                                         ->arrayNode('post_sets')
                                             ->addDefaultsIfNotSet()
                                             ->children()
                                                ->scalarNode('provider')->end()
                                             ->end() #--> end children
                                         ->end() #--> post_sets
                                         ->arrayNode('post_sets_has_posts')
                                             ->addDefaultsIfNotSet()
                                             ->children()
                                                ->scalarNode('provider')->end()
                                                ->arrayNode('settings')
                                                    ->useAttributeAsKey('id')
                                                    ->prototype('array')
                                                        ->children()
                                                            ->arrayNode('params')
                                                                ->prototype('array')
                                                                    ->children()
                                                                        ->scalarNode('key')->end()
                                                                        ->scalarNode('value')->end()
                                                                    ->end()
                                                                ->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end() #--> end settings
                                             ->end() #--> end children
                                         ->end() #--> end post_sets_has_posts
                                    ->end()
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
