<?php

namespace Zenstruck\MediaBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zenstruck\MediaBundle\DependencyInjection\ZenstruckMediaExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckMediaExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $config = array(
            'filesystems' => array(
                'files' => array(
                    'root_dir' => '/files',
                    'web_prefix' => '/files'
                ),
                'images' => array(
                    'root_dir' => '/images',
                    'web_prefix' => '/images'
                )
            )
        );

        $container = $this->load(array($config));

        $this->assertTrue($container instanceof ContainerBuilder);
        $this->assertTrue($container->hasDefinition('zenstruck_media.filesystem_factory'));
        $this->assertFalse($container->hasDefinition('zenstruck_media.permission_provider'));
        $this->assertFalse($container->hasDefinition('zenstruck_media.media_type'));
        $this->assertFalse($container->hasDefinition('zenstruck_media.slugify_filename_filter'));

        $factoryDefinition = $container->getDefinition('zenstruck_media.filesystem_factory');
        $calls = $factoryDefinition->getMethodCalls();
        $this->assertEquals('addFilesystem', $calls[0][0]);
        $this->assertEquals('addFilesystem', $calls[1][0]);
    }

    public function testMediaFormAndPermissions()
    {
        $config = array(
            'media_form_type' => true,
            'role_permissions' => true,
            'filesystems' => array(
                'files' => array(
                    'root_dir' => '/files',
                    'web_prefix' => '/files'
                )
            )
        );

        $container = $this->load(array($config));

        $this->assertTrue($container->hasDefinition('zenstruck_media.permission_provider'));
        $this->assertTrue($container->hasDefinition('zenstruck_media.media_type'));
    }

    public function testFilenameFilter()
    {
        $config = array(
            'slugify_filename_filter' => true,
            'filesystems' => array(
                'files' => array(
                    'root_dir' => '/files',
                    'web_prefix' => '/files'
                )
            )
        );

        $container = $this->load(array($config), array('ZenstruckSlugifyBundle'));
        $this->assertTrue($container->hasDefinition('zenstruck_media.slugify_filename_filter'));
    }

    public function testFilenameFilterNoSlugifyBundle()
    {
        $this->setExpectedException('Exception', 'ZenstruckSlugifyBundle must be installed in order to use the "slugify_filename_filter" type.');

        $config = array(
            'slugify_filename_filter' => true,
            'filesystems' => array(
                'files' => array(
                    'root_dir' => '/files',
                    'web_prefix' => '/files'
                )
            )
        );

        $this->load(array($config));
    }

    public function testLoadNoFilesystems()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->load(array(array()));
    }

    private function load(array $configs, $bundles = array())
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array_flip($bundles));

        $extension = new ZenstruckMediaExtension();
        $extension->load($configs, $container);

        return $container;
    }
}
