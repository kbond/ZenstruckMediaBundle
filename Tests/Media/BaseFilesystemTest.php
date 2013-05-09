<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Zenstruck\MediaBundle\Media\Alert\NullAlertProvider;
use Zenstruck\MediaBundle\Media\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Zenstruck\MediaBundle\Media\FilesystemManager;
use Zenstruck\MediaBundle\Media\Permission\BooleanPermissionProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseFilesystemTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $filesystem = new SymfonyFilesystem();
        $filesystem->mirror(__DIR__.'/../Fixtures/files', $this->getTempFixtureDir());
    }

    protected function tearDown()
    {
        $filesystem = new SymfonyFilesystem();
        $filesystem->remove($this->getTempFixtureDir());
    }

    protected function createFilesystem($path = null, $rootDir = null, $webPrefix = '/files')
    {
        return new Filesystem($path, $this->getTempFixtureDir().$rootDir, $webPrefix);
    }

    protected function createFilesystemManager($name = 'default', $params = array(), $path = null, $rootDir = null, $webPrefix = '/files', $alertProvider = null, $permissionProvider = null, $allowedExtensions = null)
    {
        if (!$alertProvider) {
            $alertProvider = new NullAlertProvider();
        }

        if (!$permissionProvider) {
            $permissionProvider = new BooleanPermissionProvider();
        }

        return new FilesystemManager($name, $params, $this->createFilesystem($path, $rootDir, $webPrefix), $alertProvider, $permissionProvider, $allowedExtensions);
    }

    protected function getTempFixtureDir()
    {
        return sys_get_temp_dir().'/Fixures/';
    }
}