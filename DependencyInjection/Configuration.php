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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('rz_news');
        $this->addBundleSettings($node);
//        $this->addAdminSettings($node);
        return $treeBuilder;
    }

        /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addBundleSettings(ArrayNodeDefinition $node)
    {
        /**
         * TODO: refactor as not to copy the whole configuration of SonataUserBundle
         * This section will allow RzBundle to override SonataUserBundle via rz_user configuration
         */
        $supportedManagerTypes = array('orm', 'mongodb');

        $node
            ->children()
                ->scalarNode('manager_type')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedManagerTypes)
                        ->thenInvalid('The manager type %s is not supported. Please choose one of '.json_encode($supportedManagerTypes))
                    ->end()
                ->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('tag')->defaultValue('Application\\Sonata\\ClassificationBundle\\Entity\\Tag')->end()
                        ->scalarNode('collection')->defaultValue('Application\\Sonata\\ClassificationBundle\\Entity\\Collection')->end()
                        ->scalarNode('post')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\Post')->end()
                        ->scalarNode('post_has_category')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\PostHasCategory')->end()
                        ->scalarNode('category')->defaultValue('Application\\Sonata\\ClassificationBundle\\Entity\\Category')->end()
                        ->scalarNode('comment')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\Comment')->end()
                        ->scalarNode('media')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\Media')->end()
                        ->scalarNode('user')->defaultValue('Application\\Sonata\\UserBundle\\Entity\\User')->end()
                    ->end()
                ->end()
                ->arrayNode('class_manager')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('post')->defaultValue('Rz\\NewsBundle\\Entity\\PostManager')->end()
                        ->scalarNode('post_has_category')->defaultValue('Rz\\NewsBundle\\Entity\\PostHasCategoryManager')->end()
                        ->scalarNode('comment')->defaultValue('Sonata\\NewsBundle\\Entity\\CommentManager')->end()
                    ->end()
                ->end()
                ->arrayNode('settings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('news_pager_max_per_page')->defaultValue(5)->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('post')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('RzNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('RzNewsBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_has_category')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostHasCategoryAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('RzNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('comment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\CommentAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('RzNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('RzNewsBundle:CommentAdmin:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('RzNewsBundle:CommentAdmin:edit.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('rz_list_inner_row_header')->defaultValue('RzNewsBundle:CRUD:list_inner_row_header.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('rz_list_field_header')->defaultValue('RzNewsBundle:CRUD:list_field_header.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('batch')->defaultValue('RzAdminBundle:CRUD:list__batch.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('blocks')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('class')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('recent_posts')->defaultValue('Rz\\NewsBundle\\Block\\RecentPostsBlockService')->end()
                                ->scalarNode('recent_comments')->defaultValue('Rz\\NewsBundle\\Block\\RecentCommentsBlockService')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('news_layout')->defaultValue('RzNewsBundle:Post:news_layout.html.twig')->end()
                        ->scalarNode('view')->defaultValue('RzNewsBundle:Post:view.html.twig')->end()
                        ->scalarNode('archive_rss')->defaultValue('RzNewsBundle:Post:archive.rss.twig')->end()
                        ->scalarNode('archive_html')->defaultValue('RzNewsBundle:Post:archive.html.twig')->end()
                        ->scalarNode('comment_form')->defaultValue('RzNewsBundle:Post:comment_form.html.twig')->end()
                        ->scalarNode('comments')->defaultValue('RzNewsBundle:Post:comments.html.twig')->end()
                        ->scalarNode('category_html')->defaultValue('RzNewsBundle:Post:category.html.twig')->end()
                        ->scalarNode('category_rss')->defaultValue('RzNewsBundle:Post:category.rss.twig')->end()
                    ->end()
                ->end()
            ->end();
    }
}
