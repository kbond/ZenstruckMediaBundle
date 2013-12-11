<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\MediaBundle\Media\Filesystem;
use Zenstruck\MediaBundle\Media\Filter\SlugifyFilenameFilter;
use Zenstruck\MediaBundle\Media\Permission\BooleanPermissionProvider;

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
        $filesystem = new Filesystem('default', 'copy/A', sys_get_temp_dir().'/Fixtures', '/files', new BooleanPermissionProvider());
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

        $filesystem = new Filesystem('default', null, '/', '/', new BooleanPermissionProvider());

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
        $filesystem->rename('dolor.txt', 'foo.txt');

        $this->assertFileExists($this->getTempFixtureDir().'foo.txt');
        $this->assertFileNotExists($this->getTempFixtureDir().'dolor.txt');

        $filesystem->rename('ipsum.txt', 'bar.dat');
        $this->assertFileExists($this->getTempFixtureDir().'bar.txt');

        $filesystem = $this->createFilesystem('A');
        $filesystem->rename('a.dat', 'foo.dat');

        $this->assertFileExists($this->getTempFixtureDir().'A/foo.dat');
        $this->assertTrue(is_file($this->getTempFixtureDir().'A/foo.dat'));

        $filesystem = $this->createFilesystem('A');
        $filesystem->rename('B', 'Foo');

        $this->assertFileExists($this->getTempFixtureDir().'A/Foo');
        $this->assertTrue(is_dir($this->getTempFixtureDir().'A/Foo'));
    }

    public function testRenameFileNoPermission()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\AccessDeniedException',
            'You do not have the required permissions to rename files.'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', null, false);
        $filesystem->rename('dolor.txt', 'foo.txt');
    }

    public function testRenameDirNoPermission()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\AccessDeniedException',
            'You do not have the required permissions to rename directories.'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', null, false);
        $filesystem->rename('A', 'B');
    }

    public function testRenameFileNotFound()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'The file/directory "foo.txt" does not exist.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->rename('foo.txt', 'bar.txt');
    }

    public function testRenameFileSameName()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'You didn\'t specify a new name for "dolor.txt".'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->rename('dolor.txt', 'dolor.txt');
    }

    public function testRenameFileExists()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'The file "dolor.txt" already exists.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->rename('ipsum.txt', 'dolor.txt');
    }

    public function testDeleteFile()
    {
        $filesystem = $this->createFilesystem();

        $this->assertFileExists($this->getTempFixtureDir().'dolor.txt');
        $filesystem->delete('dolor.txt');
        $this->assertFileNotExists($this->getTempFixtureDir().'dolor.txt');

        $filesystem = $this->createFilesystem('A');
        $this->assertFileExists($this->getTempFixtureDir().'A/B');
        $filesystem->delete('B');
        $this->assertFileNotExists($this->getTempFixtureDir().'A/B');
    }

    public function testDeleteFileNoPermission()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\AccessDeniedException',
            'You do not have the required permissions to delete files.'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', null, false);
        $filesystem->delete('dolor.txt');
    }

    public function testDeleteDirNoPermission()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\AccessDeniedException',
            'You do not have the required permissions to delete directories.'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', null, false);
        $filesystem->delete('A');
    }

    public function testDeleteFileNotFound()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'No file/directory named "foo.txt".'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->delete('foo.txt');
    }

    public function testMkdir()
    {
        $filesystem = $this->createFilesystem();

        $this->assertFileNotExists($this->getTempFixtureDir().'foo');
        $filesystem->mkdir('foo');
        $this->assertFileExists($this->getTempFixtureDir().'foo');

        $filesystem = $this->createFilesystem('A');
        $this->assertFileNotExists($this->getTempFixtureDir().'A/foo');
        $filesystem->mkdir('foo');
        $this->assertFileExists($this->getTempFixtureDir().'A/foo');
        $this->assertTrue(is_dir($this->getTempFixtureDir().'A/foo'));
    }

    public function testMkdirNoPermission()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\AccessDeniedException',
            'You do not have the required permissions to create directories.'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', null, false);
        $filesystem->mkdir('A');
    }

    public function testMkdirNoName()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'You didn\'t enter a directory name.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->mkdir('');
    }

    public function testMkdirExists()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'Failed to create directory "A".  It already exists.'
        );

        $filesystem = $this->createFilesystem();
        $filesystem->mkdir('A');
    }

    public function testUploadFile()
    {
        $filesystem = $this->createFilesystem();
        $tempFile = sys_get_temp_dir().'/foo.txt';
        touch($tempFile);

        $file = new UploadedFile($tempFile, 'foo.txt', null, null, null, true);

        $this->assertFileNotExists($this->getTempFixtureDir().'foo.txt');
        $filesystem->uploadFile($file);
        $this->assertFileExists($this->getTempFixtureDir().'foo.txt');

        touch($tempFile);

        $filesystem = $this->createFilesystem('A');
        $this->assertFileNotExists($this->getTempFixtureDir().'A/foo.txt');
        $filesystem->uploadFile($file);
        $this->assertFileExists($this->getTempFixtureDir().'A/foo.txt');
    }

    public function testUploadFileNoPermission()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\AccessDeniedException',
            'You do not have the required permissions to upload files.'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', null, false);
        $tempFile = sys_get_temp_dir().'/foo.txt';
        touch($tempFile);
        $file = new UploadedFile($tempFile, 'foo.txt', null, null, null, true);

        $filesystem->uploadFile($file);
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

    public function testUploadFileBadExtension()
    {
        $this->setExpectedException(
            'Zenstruck\MediaBundle\Exception\Exception',
            'The extension "txt" is not allowed. Valid extensions: "jpg, gif"'
        );

        $filesystem = $this->createFilesystem(null, null, '/files', 'jpg,gif');
        $tempFile = sys_get_temp_dir().'/foo.txt';
        touch($tempFile);

        $file = new UploadedFile($tempFile, 'foo.txt', null, null, null, true);
        $filesystem->uploadFile($file);
    }

    public function testSlugify()
    {
        $filesystem = $this->createFilesystem();
        $filesystem->addFilenameFilter(new SlugifyFilenameFilter(new Slugify()));

        $filesystem->rename('dolor.txt', 'foo bar!.txt');
        $this->assertFileExists($this->getTempFixtureDir().'foo-bar.txt');

        $filesystem->mkdir('Foo Bar!');
        $this->assertFileExists($this->getTempFixtureDir().'foo-bar');

        $tempFile = sys_get_temp_dir().'/foo baz.txt';
        touch($tempFile);

        $file = new UploadedFile($tempFile, 'foo baz.txt', null, null, null, true);
        $filesystem->uploadFile($file);

        $this->assertFileExists($this->getTempFixtureDir().'foo-baz.txt');
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
