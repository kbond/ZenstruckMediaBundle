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

        $factoryDefinition = $container->getDefinition('zenstruck_media.filesystem_factory');

        if ($config['media_form_type']) {
            $loader->load('media_type.xml');
        }

        if ($config['role_permissions']) {
            $loader->load('role_permissions.xml');
            $factoryDefinition->addArgument($container->getDefinition('zenstruck_media.permission_provider'));
        }

        foreach ($config['filesystems'] as $name => $filesystemConfig) {
            $factoryDefinition->addMethodCall('addManager', array($name, $filesystemConfig));
        }
    }
}