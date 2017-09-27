<?php

namespace NewsBundle\DependencyInjection;

use NewsBundle\Generator\HeadMetaGenerator;
use NewsBundle\Generator\LinkGenerator;
use NewsBundle\Generator\RelatedEntriesGenerator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('news');
        $rootNode
            ->children()
                ->arrayNode('relations')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('NewsBundle\Generator\HeadMetaGenerator')->defaultValue(HeadMetaGenerator::class)->end()
                        ->scalarNode('NewsBundle\Generator\LinkGenerator')->defaultValue(LinkGenerator::class)->end()
                        ->scalarNode('NewsBundle\Generator\RelatedEntriesGenerator')->defaultValue(RelatedEntriesGenerator::class)->end()
                    ->end()
                ->end()
                ->arrayNode('list')
                    ->isRequired()
                    ->children()
                        ->scalarNode('sort_by')->end()
                        ->scalarNode('order_by')->end()
                        ->scalarNode('time_range')->end()
                        ->integerNode('max_items')->end()
                        ->arrayNode('paginate')
                            ->children()
                                ->integerNode('items_per_page')->end()
                            ->end()
                        ->end()
                        ->arrayNode('layouts')
                            ->children()
                                ->scalarNode('default')->end()
                                ->arrayNode('items')
                                    ->useAttributeAsKey('id')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('detail')
                    ->children()
                    ->end()
                ->end()
                ->arrayNode('entry_types')
                    ->children()
                        ->scalarNode('default')->end()
                        ->arrayNode('items')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('route')->end()
                                    ->scalarNode('custom_layout_id')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
