<?php

namespace Zenstruck\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckMediaExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('zenstruck_media.default_layout', $config['default_layout']);
        $container->setParameter('zenstruck_media.filesystem.class', $config['filesystem_class']);
        $bundles = $container->getParameter('kernel.bundles');

        $factoryDefinition = $container->getDefinition('zenstruck_media.filesystem_factory');

        if ($config['slugify_filename_filter']) {
            $loader->load('slugify_filename_filter.xml');
            $slugifyFilterDefinition = $container->getDefinition('zenstruck_media.slugify_filename_filter');

            switch (true) {
                case isset($bundles['ZenstruckSlugifyBundle']):
                    $slugifyFilterDefinition->replaceArgument(0, new Reference('zenstruck.slugify'));
                    break;

                case isset($bundles['CocurSlugifyBundle']):
                    $slugifyFilterDefinition->replaceArgument(0, new Reference('cocur_slugify'));
                    break;

                default:
                    throw new \LogicException('CocurSlugifyBundle or ZenstruckSlugifyBundle must be installed in order to use the "slugify_filename_filter" type.');
            }
        }

        if ($config['media_form_type']) {
            $loader->load('media_type.xml');
        }

        if ($config['role_permissions']) {
            $loader->load('role_permissions.xml');
            $factoryDefinition->addArgument($container->getDefinition('zenstruck_media.permission_provider'));
        }

        foreach ($config['filesystems'] as $name => $filesystemConfig) {
            $factoryDefinition->addMethodCall('addFilesystem', array($name, $filesystemConfig));
        }
    }
}
