<?php

namespace Zenstruck\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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
            if (!array_key_exists('ZenstruckSlugifyBundle', $bundles)) {
                throw new \Exception('ZenstruckSlugifyBundle must be installed in order to use the "slugify_filename_filter" type.');
            }

            $loader->load('slugify_filename_filter.xml');
        }

        if ($config['media_form_type']) {
            $loader->load('media_type.xml');
        }

        if ($config['role_permissions']) {
            $loader->load('role_permissions.xml');
            $factoryDefinition->addArgument($container->getDefinition('zenstruck_media.permission_provider'));
        }

        foreach ($config['filesystems'] as $name => $filesystemConfig) {
            if ($filesystemConfig['secure'] && !array_key_exists('IgorwFileServeBundle', $bundles)) {
                throw new \Exception('IgorwFileServeBundle must be installed in order to use "secure" filesystems.');
            }

            $factoryDefinition->addMethodCall('addFilesystem', array($name, $filesystemConfig));
        }
    }
}