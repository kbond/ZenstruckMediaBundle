<?php

namespace Zenstruck\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zenstruck_media');

        $rootNode
            ->children()
                ->scalarNode('default_layout')->defaultValue('ZenstruckMediaBundle:Twitter:default_layout.html.twig')->end()
                ->booleanNode('media_form_type')->defaultFalse()->end()
                ->booleanNode('role_permissions')->defaultFalse()->end()
                ->arrayNode('filesystems')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('root_dir')->defaultValue('%kernel.root_dir%/../web/files')->isRequired()->end()
                            ->scalarNode('web_prefix')->defaultValue('/files')->isRequired()->end()
                            ->scalarNode('filesystem_class')->defaultValue('Zenstruck\MediaBundle\Media\Filesystem')->end()
                            ->scalarNode('filesystem_manager_class')->defaultValue('Zenstruck\MediaBundle\Media\FilesystemManager')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}