<?php

namespace Zenstruck\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilenameFilterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zenstruck_media.filesystem_factory')) {
            return;
        }

        $definition = $container->getDefinition(
            'zenstruck_media.filesystem_factory'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'zenstruck_media.filename_filter'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addFilenameFilter', array(new Reference($id)));
        }
    }
}
