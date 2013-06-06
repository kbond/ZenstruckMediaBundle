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
                ->booleanNode('slugify_filename_filter')->defaultFalse()->end()
                ->scalarNode('filesystem_class')->defaultValue('Zenstruck\MediaBundle\Media\Filesystem')->end()
                ->booleanNode('media_form_type')->defaultFalse()->end()
                ->booleanNode('role_permissions')->defaultFalse()->end()
                ->arrayNode('filesystems')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('root_dir')->defaultValue('%kernel.root_dir%/../web/files')->isRequired()->end()
                            ->scalarNode('web_prefix')->defaultValue('/files')->isRequired()->end()
                            ->scalarNode('allowed_extensions')->defaultNull()->info('Comma separated list of extensions')->example('jpg,gif,png')->end()
                            ->scalarNode('secure')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
