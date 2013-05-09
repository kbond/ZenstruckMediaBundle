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