<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Zenstruck\MediaBundle\Media\File;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FileTest extends BaseFilesystemTest
{
    public function testIsImage()
    {
        $file = $this->createFile('dolor.txt');
        $this->assertFalse($file->isImage());

        touch($this->getTempFixtureDir().'foo.jpg');
        $file = $this->createFile('foo.jpg');
        $this->assertTrue($file->isImage());
    }

    public function testWebPrefix()
    {
        $file = $this->createFile('dolor.txt');
        $this->assertEquals('/foo/dolor.txt', $file->getWebPath());

        $file = $this->createFile('dolor.txt', '/foo/');
        $this->assertEquals('/foo/dolor.txt', $file->getWebPath());
    }

    public function testSize()
    {
        $file = $this->createFile('dolor.txt');

        $this->assertEquals('29 B', $file->getSize());
    }

    public function testIsNew()
    {
        $file = $this->createFile('dolor.txt');

        $this->assertTrue($file->isNew());
    }

    public function testWrapper()
    {
        $file = $this->createFile('dolor.txt');

        $this->assertEquals('dolor.txt', $file->getFilename());
        $this->assertEquals('txt', $file->getExtension());
    }

    protected function createFile($name, $webPrefix = '/foo')
    {
        return new File(new \SplFileInfo($this->getTempFixtureDir().$name), $webPrefix);
    }
}
