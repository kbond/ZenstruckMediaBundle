<?php

namespace Zenstruck\MediaBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Zenstruck\MediaBundle\DependencyInjection\Compiler\FilenameFilterCompilerPass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilenameFilterCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessShouldRegisterTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass = new FilenameFilterCompilerPass();

        $registryDefinition = new Definition();

        $container->setDefinition('zenstruck_media.filesystem_factory', $registryDefinition);
        $container->setDefinition('fitler.foo.a', $this->createProviderDefinition());
        $container->setDefinition('fitler.foo.b', $this->createProviderDefinition());

        $pass->process($container);
        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals('addFilenameFilter', $calls[0][0]);
        $this->assertEquals('addFilenameFilter', $calls[1][0]);
    }

    private function createProviderDefinition()
    {
        $provider = $this->getMock('Zenstruck\MediaBundle\Media\Filter\FilenameFilterInterface');

        $definition = new Definition(get_class($provider));
        $definition->addTag('zenstruck_media.filename_filter');

        return $definition;
    }
}
