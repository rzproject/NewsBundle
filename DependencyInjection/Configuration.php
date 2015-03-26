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
use Rz\NewsBundle\Entity\BasePost;

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
        $this->addSettingsSection($node);
        if (interface_exists('Sonata\PageBundle\Model\PageInterface')) {
            $this->addBlockSettings($node);
        }
        return $treeBuilder;
    }

     /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addSettingsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('default_collection')->isRequired()->end()
                ->arrayNode('collections')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('provider')->isRequired()->end()
                            ->scalarNode('default_template')->isRequired()->end()
                            ->arrayNode('templates')
                                ->isRequired()
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->defaultValue('default')->end()
                                        ->scalarNode('path')->defaultValue('RzNewsBundle:Post:view.html.twig')->end()
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
                        ->scalarNode('post_has_media')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\PostHasMedia')->end()
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
                        ->scalarNode('post_has_media')->defaultValue('Rz\\NewsBundle\\Entity\\PostHasMediaManager')->end()
                        ->scalarNode('comment')->defaultValue('Sonata\\NewsBundle\\Entity\\CommentManager')->end()
                    ->end()
                ->end()
                ->arrayNode('settings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('news_pager_max_per_page')->defaultValue(5)->end()
                        ->scalarNode('force_html_extension_on_url')->defaultValue(true)->end()
                        ->scalarNode('ajax_pagination')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('post')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('RzNewsBundle:PostAdmin')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('RzNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('RzNewsBundle:PostAdmin:list.html.twig')->cannotBeEmpty()->end()
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
                                        ->scalarNode('list')->defaultValue('RzNewsBundle:PostHasCategoryAdmin:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('RzNewsBundle:PostHasCategoryAdmin:edit.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_has_media')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostHasMediaAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('RzNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('RzNewsBundle:PostHasMediaAdmin:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('inner_list_row')->defaultValue('RzNewsBundle:PostHasMediaAdmin:masonry_item.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('RzNewsBundle:PostHasMediaAdmin:edit.html.twig')->cannotBeEmpty()->end()
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
                        ->arrayNode('collection')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Block\\PostByCollectionBlockService')->end()
                                ->arrayNode('templates')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->defaultValue('default')->end()
                                            ->scalarNode('path')->defaultValue('RzNewsBundle:Block:post_by_collection_list.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('recent_posts')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Block\\RecentPostsBlockService')->end()
                                ->arrayNode('templates')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->defaultValue('default')->end()
                                            ->scalarNode('path')->defaultValue('RzNewsBundle:Block:recent_posts.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('recent_comments')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Block\\RecentCommentsBlockService')->end()
                                ->arrayNode('templates')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->defaultValue('default')->end()
                                            ->scalarNode('path')->defaultValue('RzNewsBundle:Block:recent_comments.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('news_layout_html')->defaultValue('RzNewsBundle:Post:news_layout.html.twig')->end()
                        ->scalarNode('news_layout_rss')->defaultValue('RzNewsBundle:Post:news_layout.rss.twig')->end()
                        ->scalarNode('view')->defaultValue('RzNewsBundle:Post:view.html.twig')->end()
                        ->scalarNode('archive_rss')->defaultValue('RzNewsBundle:Post:archive.rss.twig')->end()
                        ->scalarNode('archive_html')->defaultValue('RzNewsBundle:Post:archive.html.twig')->end()
                        ->scalarNode('category_html')->defaultValue('RzNewsBundle:Post:category_list_default.html.twig')->end()
                        ->scalarNode('category_rss')->defaultValue('RzNewsBundle:Post:category.rss.twig')->end()
                        ->scalarNode('collection_html')->defaultValue('RzNewsBundle:Post:collection_list_default.html.twig')->end()
                        ->scalarNode('collection_html_ajax')->defaultValue('RzNewsBundle:Post:collection_list_default_ajax.html.twig')->end()
                        ->scalarNode('collection_html_ajax_pager')->defaultValue('RzNewsBundle:Post:collection_list_default_ajax_pager.html.twig')->end()
                        ->scalarNode('collection_rss')->defaultValue('RzNewsBundle:Post:collection.rss.twig')->end()
                        ->scalarNode('tag_html')->defaultValue('RzNewsBundle:Post:tag_list_default.html.twig')->end()
                        ->scalarNode('tag_rss')->defaultValue('RzNewsBundle:Post:tag.rss.twig')->end()
                        ->scalarNode('comment_form')->defaultValue('RzNewsBundle:Post:comment_form.html.twig')->end()
                        ->scalarNode('comments')->defaultValue('RzNewsBundle:Post:comments.html.twig')->end()
                    ->end()
                ->end()

                ->arrayNode('route')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('class')->defaultValue('Rz\\NewsBundle\\Route\\CmsNewsRouter')->end()
                        ->scalarNode('sequence')->defaultValue(array(BasePost::ROUTE_CLASSIFICATION_SEQ_COLLECTION, BasePost::ROUTE_CLASSIFICATION_SEQ_TAG, BasePost::ROUTE_CLASSIFICATION_SEQ_CATEGORY, BasePost::ROUTE_CLASSIFICATION_SEQ_POST))->end()
                    ->end()
                ->end()

            ->end();
    }


    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addBlockSettings(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('blocks')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('collection')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Block\\PostByCollectionBlockService')->end()
                                ->arrayNode('templates')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->defaultValue('default')->end()
                                            ->scalarNode('path')->defaultValue('RzNewsBundle:Block:post_by_collection_list.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('recent_posts')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Block\\RecentPostsBlockService')->end()
                                ->arrayNode('templates')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->defaultValue('default')->end()
                                            ->scalarNode('path')->defaultValue('RzNewsBundle:Block:recent_posts.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('recent_comments')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Block\\RecentCommentsBlockService')->end()
                                ->arrayNode('templates')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->defaultValue('default')->end()
                                            ->scalarNode('path')->defaultValue('RzNewsBundle:Block:recent_comments.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()

            ->end();
    }
}
