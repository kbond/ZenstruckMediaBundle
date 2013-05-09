<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\MediaBundle\Media\FilesystemManager;
use Zenstruck\MediaBundle\Media\Filter\SlugableFilenameFilter;
use Zenstruck\MediaBundle\Media\Permission\BooleanPermissionProvider;
use Zenstruck\MediaBundle\Tests\Fixtures\Alert\TestAlertProvider;

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

    /**
     * @dataProvider fileUploadProvider
     */
    public function testFileUpload($filename, $allowedExtensions, $message, $type, $permission, $slugFilter)
    {
        $alerts = new TestAlertProvider();
        $permission = new BooleanPermissionProvider($permission);

        $tempFile = sys_get_temp_dir().'/'.$filename;
        touch($tempFile);
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts, $permission, $allowedExtensions);

        if ($slugFilter) {
            $filesystem->addFilenameFilter(new SlugableFilenameFilter(new Slugify()));
        }

        $file = new UploadedFile($tempFile, $filename, null, null, null, true);

        $filesystem->uploadFile($file);

        $this->assertEquals($message, $alerts->message);
        $this->assertEquals($type, $alerts->type);
    }

    public function testFileUploadNoFile()
    {
        $alerts = new TestAlertProvider();
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts);

        $filesystem->uploadFile('');

        $this->assertEquals('No file selected.', $alerts->message);
        $this->assertEquals('error', $alerts->type);

        $filesystem->uploadFile('foo.txt');

        $this->assertEquals('No file selected.', $alerts->message);
        $this->assertEquals('error', $alerts->type);
    }

    /**
     * @dataProvider mkDirProvider
     */
    public function testMkDir($dirname, $message, $type, $permissions)
    {
        $alerts = new TestAlertProvider();
        $permissions = new BooleanPermissionProvider($permissions);
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts, $permissions);

        $filesystem->mkDir($dirname);

        $this->assertEquals($message, $alerts->message);
        $this->assertEquals($type, $alerts->type);
    }

    /**
     * @dataProvider deleteFileProvider
     */
    public function testDeleteFile($filename, $message, $type, $permissions)
    {
        $alerts = new TestAlertProvider();
        $permissions = new BooleanPermissionProvider($permissions);
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts, $permissions);

        $filesystem->deleteFile($filename);

        $this->assertEquals($message, $alerts->message);
        $this->assertEquals($type, $alerts->type);
    }

    /**
     * @dataProvider deleteDirProvider
     */
    public function testDeleteDir($filename, $message, $type, $permissions)
    {
        $alerts = new TestAlertProvider();
        $permissions = new BooleanPermissionProvider($permissions);
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts, $permissions);

        $filesystem->deleteDir($filename);

        $this->assertEquals($message, $alerts->message);
        $this->assertEquals($type, $alerts->type);
    }

    /**
     * @dataProvider renameFileProvider
     */
    public function testRenameFile($oldName, $newName, $message, $type, $permissions)
    {
        $alerts = new TestAlertProvider();
        $permissions = new BooleanPermissionProvider($permissions);
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts, $permissions);

        $filesystem->renameFile($oldName, $newName);

        $this->assertEquals($message, $alerts->message);
        $this->assertEquals($type, $alerts->type);
    }

    /**
     * @dataProvider renameDirProvider
     */
    public function testDirFile($oldName, $newName, $message, $type, $permissions)
    {
        $alerts = new TestAlertProvider();
        $permissions = new BooleanPermissionProvider($permissions);
        $filesystem = $this->createFilesystemManager('default', array(), null, null, '/files', $alerts, $permissions);

        $filesystem->renameDir($oldName, $newName);

        $this->assertEquals($message, $alerts->message);
        $this->assertEquals($type, $alerts->type);
    }

    public function mkDirProvider()
    {
        return array(
            array('B', 'Directory "B" created.', 'success', true),
            array('A', 'Failed to create directory "A".  It already exists.', 'error', true),
            array('B', 'You do not have the required permission to create directories.', 'error', false)
        );
    }

    public function deleteFileProvider()
    {
        return array(
            array('dolor.txt', 'File "dolor.txt" deleted.', 'success', true),
            array('foo.txt', 'No file/directory named "foo.txt".', 'error', true),
            array('dolor.txt', 'You do not have the required permission to delete files.', 'error', false)
        );
    }

    public function deleteDirProvider()
    {
        return array(
            array('A', 'Directory "A" deleted.', 'success', true),
            array('C', 'No file/directory named "C".', 'error', true),
            array('A', 'You do not have the required permission to delete directories.', 'error', false)
        );
    }

    public function renameFileProvider()
    {
        return array(
            array('dolor.txt', 'foo.txt', 'File "dolor.txt" renamed to "foo.txt".', 'success', true),
            array('dolor.txt', 'ipsum.txt', 'The file "ipsum.txt" already exists.', 'error', true),
            array('nofile.txt', 'foo.txt', 'The file/directory "nofile.txt" does not exist.', 'error', true),
            array('dolor.txt', 'dolor.txt', 'You didn\'t specify a new name for "dolor.txt".', 'error', true),
            array('dolor.txt', 'foo.txt', 'You do not have the required permission to rename files.', 'error', false),
        );
    }

    public function renameDirProvider()
    {
        return array(
            array('A', 'B', 'Directory "A" renamed to "B".', 'success', true),
            array('A', 'copy', 'The directory "copy" already exists.', 'error', true),
            array('J', 'B', 'The file/directory "J" does not exist.', 'error', true),
            array('A', 'A', 'You didn\'t specify a new name for "A".', 'error', true),
            array('A', 'B', 'You do not have the required permission to rename directories.', 'error', false),
        );
    }

    public function fileUploadProvider()
    {
        return array(
            array('foo.txt', 'jpg,gif', 'The extension "txt" is not allowed. Valid extensions: "jpg, gif"', 'error', true, false),
            array('foo.txt', 'jpg, gif', 'The extension "txt" is not allowed. Valid extensions: "jpg, gif"', 'error', true, false),
            array('foo.txt', '', 'File "foo.txt" uploaded.', 'success', true, false),
            array('foo.txt', null, 'File "foo.txt" uploaded.', 'success', true, false),
            array('foo.txt', 'txt,jpg', 'File "foo.txt" uploaded.', 'success', true, false),
            array('dolor.txt', null, 'File "dolor.txt" already exists.', 'error', true, false),
            array('foo.txt', null, 'You do not have the required permission to upload files.', 'error', false, false),
            array('BaZ eR.txT', null, 'File "BaZ eR.txt" uploaded.', 'success', true, false),
            array('BaZ eR.txT', null, 'File "baz-er.txt" uploaded.', 'success', true, true),
        );
    }
}