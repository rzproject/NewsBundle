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
                        ->scalarNode('tag')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\Tag')->end()
                        ->scalarNode('category')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\Category')->end()
                        ->scalarNode('post')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\Post')->end()
                        ->scalarNode('comment')->defaultValue('Application\\Sonata\\NewsBundle\\Entity\\Comment')->end()
                    ->end()
                ->end()
                ->arrayNode('class_manager')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('tag')->defaultValue('Sonata\\NewsBundle\\Entity\\TagManager')->end()
                        ->scalarNode('category')->defaultValue('Sonata\\NewsBundle\\Entity\\CategoryManager')->end()
                        ->scalarNode('post')->defaultValue('Rz\\NewsBundle\\Entity\\PostManager')->end()
                        ->scalarNode('comment')->defaultValue('Sonata\\NewsBundle\\Entity\\CommentManager')->end()
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
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('category')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\CategoryAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('comment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\CommentAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('tag')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\TagAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
                                        ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }


//    /**
//     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
//     */
//    private function addAdminSettings(ArrayNodeDefinition $node)
//    {
//        $node
//            ->children()
//                ->arrayNode('orm')
//                ->addDefaultsIfNotSet()
//                ->children()
//                    ->arrayNode('admin')
//                        ->addDefaultsIfNotSet()
//                        ->children()
//                            ->arrayNode('post')
//                                ->addDefaultsIfNotSet()
//                                ->children()
//                                    ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\PostAdmin')->end()
//                                    ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
//                                    ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNewsBundle')->end()
//                                    ->arrayNode('templates')
//                                        ->addDefaultsIfNotSet()
//                                        ->children()
//                                            ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
//                                        ->end()
//                                    ->end()
//                                ->end()
//                            ->end()
//                            ->arrayNode('category')
//                                ->addDefaultsIfNotSet()
//                                ->children()
//                                    ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\CategoryAdmin')->end()
//                                    ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
//                                    ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataUserBundle')->end()
//                                    ->arrayNode('templates')
//                                        ->addDefaultsIfNotSet()
//                                        ->children()
//                                            ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
//                                        ->end()
//                                    ->end()
//                                ->end()
//                            ->end()
//                            ->arrayNode('comment')
//                                ->addDefaultsIfNotSet()
//                                ->children()
//                                    ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\CommentAdmin')->end()
//                                    ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
//                                    ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataUserBundle')->end()
//                                    ->arrayNode('templates')
//                                        ->addDefaultsIfNotSet()
//                                        ->children()
//                                            ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
//                                        ->end()
//                                    ->end()
//                                ->end()
//                            ->end()
//                            ->arrayNode('tag')
//                                ->addDefaultsIfNotSet()
//                                ->children()
//                                    ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\NewsBundle\\Admin\\TagAdmin')->end()
//                                    ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
//                                    ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataUserBundle')->end()
//                                    ->arrayNode('templates')
//                                        ->addDefaultsIfNotSet()
//                                        ->children()
//                                            ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
//                                            ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
//                                        ->end()
//                                    ->end()
//                                ->end()
//                            ->end()
//                        ->end()
//                    ->end()
//                ->end()
//            ->end()
//        ;
//    }
}
