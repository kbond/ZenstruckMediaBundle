<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\MediaBundle\Media\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Zenstruck\MediaBundle\Media\Permission\BooleanPermissionProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseFilesystemTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $filesystem = new SymfonyFilesystem();
        $filesystem->mirror(__DIR__.'/../Fixtures/files', $this->getTempFixtureDir());
    }

    protected function tearDown()
    {
        parent::tearDown();

        $filesystem = new SymfonyFilesystem();
        $filesystem->remove($this->getTempFixtureDir());
    }

    protected function createFilesystem(
        $path = null, $rootDir = null, $webPrefix = '/files', $allowedExtensions = null, $permission = true
    )
    {
        return new Filesystem('default', $path, $this->getTempFixtureDir().$rootDir,
            $webPrefix, new BooleanPermissionProvider($permission), $allowedExtensions
        );
    }

    protected function getTempFixtureDir()
    {
        return sys_get_temp_dir().'/Fixtures/';
    }
}
