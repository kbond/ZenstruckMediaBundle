<?php

namespace Zenstruck\MediaBundle\Tests\Media;

use Zenstruck\MediaBundle\Media\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

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

    protected function createFilesystem(
        $path = null, $rootDir = null, $webPrefix = '/files', $allowedExtensions = null
    )
    {
        return new Filesystem('default', $path, $this->getTempFixtureDir().$rootDir, $webPrefix, $allowedExtensions);
    }

    protected function getTempFixtureDir()
    {
        return sys_get_temp_dir().'/Fixures/';
    }
}