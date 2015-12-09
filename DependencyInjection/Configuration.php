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
        $this->addProviderSection($node);
        return $treeBuilder;
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
                ->scalarNode('default_collection')->isRequired()->end()
                ->arrayNode('collections')
                    ->useAttributeAsKey('id')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('provider')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
