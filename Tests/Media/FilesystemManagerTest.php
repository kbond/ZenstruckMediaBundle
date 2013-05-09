<?php

namespace Zenstruck\MediaBundle\Tests\Media;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilesystemManagerTest extends BaseFilesystemTest
{
    public function testGetAncestry()
    {
        $filesystem = $this->createFilesystemManager();

        $this->assertCount(0, $filesystem->getAncestry());

        $filesystem = $this->createFilesystemManager('default', array(), 'A');
        $this->assertCount(1, $filesystem->getAncestry());

        $filesystem = $this->createFilesystemManager('default', array(), 'A/B');
        $this->assertCount(2, $filesystem->getAncestry());
    }

    public function testGetPreviousPath()
    {
        $filesystem = $this->createFilesystemManager();

        $this->assertEmpty($filesystem->getPreviousPath());

        $filesystem = $this->createFilesystemManager('default', array(), 'A');
        $this->assertEmpty($filesystem->getPreviousPath());

        $filesystem = $this->createFilesystemManager('default', array(), 'A/B');
        $this->assertEquals('A', $filesystem->getPreviousPath());

        $filesystem = $this->createFilesystemManager('default', array(), 'A/B/C');
        $this->assertEquals('A/B', $filesystem->getPreviousPath());
    }

    public function testGetParameter()
    {
        $filesystem = $this->createFilesystemManager();

        $this->assertNull($filesystem->getParameter('foo'));
        $this->assertEquals('bar', $filesystem->getParameter('foo', 'bar'));

        $filesystem = $this->createFilesystemManager('default', array('foo' => 'bar'));

        $this->assertEquals('bar', $filesystem->getParameter('foo'));
        $this->assertEquals('bar', $filesystem->getParameter('foo', 'bar'));
        $this->assertEquals('foo', $filesystem->getParameter('foo', 'foo', array('baz')));
        $this->assertNull($filesystem->getParameter('foo', null, array('baz')));
    }
}