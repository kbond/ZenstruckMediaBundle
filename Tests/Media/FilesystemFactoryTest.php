<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Symfony\Component\HttpFoundation\Request;
use Zenstruck\MediaBundle\Media\FilesystemFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilesystemFactoryTest extends BaseFilesystemTest
{
    public function testAddManagerBadConfig()
    {
        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\MissingOptionsException');
        $factory = new FilesystemFactory($this->getRouter());
        $factory->addFilesystem('default', array());
    }

    public function testGetFilesystem()
    {
        $factory = new FilesystemFactory($this->getRouter());
        $factory->addFilesystem('default', array(
                'root_dir' => '/tmp',
                'web_prefix' => '/files'
            ));
        $request = new Request();

        $this->assertCount(1, $factory->getFilesystemNames());
        $this->assertEquals(array('default'), $factory->getFilesystemNames());
        $this->assertEquals('default', $factory->getFilesystem($request)->getName());
        $this->assertEquals('default', $factory->getFilesystem($request)->getName('default'));
        $this->assertEquals('default', $factory->getFilesystem($request)->getName('foo'));

        $request = new Request(array('filesystem' => 'default'));
        $this->assertEquals('default', $factory->getFilesystem($request)->getName());

        $request = new Request(array('filesystem' => 'foo'));
        $this->assertEquals('default', $factory->getFilesystem($request)->getName());

        $factory->addFilesystem('second', array(
            'root_dir' => '/tmp',
            'web_prefix' => '/files'
        ));

        $this->assertCount(2, $factory->getFilesystemNames());
        $this->assertEquals('default', $factory->getFilesystem($request)->getName());
        $this->assertEquals('default', $factory->getFilesystem($request)->getName('foo'));

        $request = new Request(array('filesystem' => 'second'));
        $this->assertEquals('second', $factory->getFilesystem($request)->getName());

        $request = new Request();
        $this->assertEquals('default', $factory->getFilesystem($request)->getName());

        $request = new Request(array('filesystem' => 'default'));
        $this->assertEquals('default', $factory->getFilesystem($request)->getName());
    }

    public function getRouter()
    {
        return $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
    }
}
