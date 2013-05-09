<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\MediaBundle\Media\Filesystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilesystemTest extends BaseFilesystemTest
{
    /**
     * @dataProvider pathProvider
     */
    public function testWorkingDir($path, $actualpath)
    {
        $filesystem = $this->createFilesystem($path);

        $this->assertEquals($this->getTempFixtureDir().$actualpath, $filesystem->getWorkingDir());
    }

    public function testWorkingDirWithoutSlash()
    {
        $filesystem = new Filesystem('copy/A', sys_get_temp_dir().'/Fixures', '/files');
        $this->assertEquals($this->getTempFixtureDir().'copy/A/', $filesystem->getWorkingDir());
    }

    public function testDirectoryNotFoundException()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\DirectoryNotFoundException',
            sprintf('Directory "%s" not found.', $this->getTempFixtureDir().'foo/')
        );
        $this->createFilesystem('foo');
    }

    public function testIsWritable()
    {
        $filesystem = $this->createFilesystem();

        $this->assertTrue($filesystem->isWritable());

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Skip on windows.');
        }

        $filesystem = new Filesystem(null, '/', '/');

        $this->assertFalse($filesystem->isWritable());
    }

    public function testInvalidDirectory()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\DirectoryNotFoundException',
            'Invalid directory.'
        );
        $this->createFilesystem('../..');
    }

    public function testGetFiles()
    {
        $filesystem = $this->createFilesystem();

        /** @var File[] $files */
        $files = $filesystem->getFiles();

        $this->assertCount(6, $files);
        $this->assertEquals('A', $files[0]->getFilename());
        $this->assertTrue($files[0]->isDir());
        $this->assertEquals('dolor.txt', $files[3]->getFilename());
        $this->assertFalse($files[3]->isDir());
        $this->assertTrue($files[3]->isFile());
        $this->assertEquals('/files/dolor.txt', $files[3]->getWebPath());

        $filesystem = $this->createFilesystem('with space');

        /** @var File[] $files */
        $files = $filesystem->getFiles();
        $this->assertCount(1, $files);
        $this->assertEquals('foo.txt', $files[0]->getFilename());
        $this->assertEquals('/files/with space/foo.txt', $files[0]->getWebPath());

        $filesystem = $this->createFilesystem('with space', null, '/');
        $files = $filesystem->getFiles();
        $this->assertEquals('/with space/foo.txt', $files[0]->getWebPath());
    }

    public function testRenameFile()
    {
        $filesystem = $this->createFilesystem();
        $filesystem->renameFile('dolor.txt', 'foo.txt');

        $this->assertFileExists($this->getTempFixtureDir().'foo.txt');
        $this->assertFileNotExists($this->getTempFixtureDir().'dolor.txt');

        $filesystem->renameFile('ipsum.txt', 'bar.dat');
        $this->assertFileExists($this->getTempFixtureDir().'bar.txt');

        $filesystem = $this->createFilesystem('A');
        $filesystem->renameFile('a.dat', 'foo.dat');

        $this->assertFileExists($this->getTempFixtureDir().'A/foo.dat');
        $this->assertTrue(is_file($this->getTempFixtureDir().'A/foo.dat'));

        $filesystem = $this->createFilesystem('A');
        $filesystem->renameFile('B', 'Foo');

        $this->assertFileExists($this->getTempFixtureDir().'A/Foo');
        $this->assertTrue(is_dir($this->getTempFixtureDir().'A/Foo'));
    }

    public function testRenameFileSameName()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'You didn\'t specify a new name for "dolor.txt".'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->renameFile('dolor.txt', 'dolor.txt');
    }

    public function testRenameFileExists()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'The file "dolor.txt" already exists.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->renameFile('ipsum.txt', 'dolor.txt');
    }

    public function testDeleteFile()
    {
        $filesystem = $this->createFilesystem();

        $this->assertFileExists($this->getTempFixtureDir().'dolor.txt');
        $filesystem->deleteFile('dolor.txt');
        $this->assertFileNotExists($this->getTempFixtureDir().'dolor.txt');

        $filesystem = $this->createFilesystem('A');
        $this->assertFileExists($this->getTempFixtureDir().'A/B');
        $filesystem->deleteFile('B');
        $this->assertFileNotExists($this->getTempFixtureDir().'A/B');
    }

    public function testMkDir()
    {
        $filesystem = $this->createFilesystem();

        $this->assertFileNotExists($this->getTempFixtureDir().'foo');
        $filesystem->mkDir('foo');
        $this->assertFileExists($this->getTempFixtureDir().'foo');

        $filesystem = $this->createFilesystem('A');
        $this->assertFileNotExists($this->getTempFixtureDir().'A/foo');
        $filesystem->mkDir('foo');
        $this->assertFileExists($this->getTempFixtureDir().'A/foo');
        $this->assertTrue(is_dir($this->getTempFixtureDir().'A/foo'));
    }

    public function testMkDirExists()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'Failed to create directory "A".  It already exists.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->mkDir('A');
    }

    public function testUploadFile()
    {
        $filesystem = $this->createFilesystem();
        $tempFile = sys_get_temp_dir().'/foo.txt';
        touch($tempFile);

        $file = new UploadedFile($tempFile, 'foo.txt', null, null, null, true);

        $this->assertFileNotExists($this->getTempFixtureDir().'foo.txt');
        $name = $filesystem->uploadFile($file);
        $this->assertFileExists($this->getTempFixtureDir().'foo.txt');
        $this->assertEquals('foo.txt', $name);

        touch($tempFile);

        $filesystem = $this->createFilesystem('A');
        $this->assertFileNotExists($this->getTempFixtureDir().'A/foo.txt');
        $filesystem->uploadFile($file);
        $this->assertFileExists($this->getTempFixtureDir().'A/foo.txt');
    }

    public function testUploadFileNoFile()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'No file selected.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->uploadFile('');
    }

    public function testUploadFileExists()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'File "dolor.txt" already exists.'
        );

        $filesystem = $this->createFilesystem();
        $tempFile = sys_get_temp_dir().'/dolor.txt';
        touch($tempFile);

        $file = new UploadedFile($tempFile, 'dolor.txt', null, null, null, true);
        $filesystem->uploadFile($file);
    }

    public function pathProvider()
    {
        return array(
            array(null, null),
            array('/', null),
            array('/copy', 'copy/'),
            array('copy', 'copy/'),
            array('copy/A', 'copy/A/'),
            array('copy/A/', 'copy/A/'),
            array('/copy/A', 'copy/A/'),
            array('with space', 'with space/')
        );
    }
}