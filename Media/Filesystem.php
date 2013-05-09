<?php

namespace Zenstruck\MediaBundle\Media;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\MediaBundle\Exception\DirectoryNotFoundException;
use Zenstruck\MediaBundle\Exception\Exception;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Filesystem
{
    protected $path;
    protected $rootDir;
    protected $webPrefix;

    protected $filesystem;
    protected $workingDir;

    public function __construct($path, $rootDir, $webPrefix)
    {
        // check for .. - user is trying to access invalid directories
        if (preg_match('#\.\.#', $path)) {
            throw new DirectoryNotFoundException(sprintf('Invalid directory.'));
        }

        $this->path = trim($path, '/');

        $this->webPrefix = trim($webPrefix) === '/' ? '/' : sprintf('/%s/', trim($webPrefix, '/'));
        $this->rootDir = rtrim($rootDir, '\\/');

        $this->filesystem = new BaseFilesystem();
        $this->workingDir = rtrim($this->rootDir.'/'.$this->path, '/').'/';

        if (!is_dir($this->workingDir)) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" not found.', $this->workingDir));
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getWorkingDir()
    {
        return $this->workingDir;
    }

    public function isWritable()
    {
        return is_writable($this->workingDir);
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        $files = Finder::create()
            ->sort(function(\SplFileInfo $a, \SplFileInfo $b) {
                    if ($a->isDir() && $b->isFile()) {
                        return -1;
                    } elseif ($a->isFile() && $b->isDir()) {
                        return 1;
                    }

                    return strcasecmp($a->getFilename(), $b->getFilename());
                })
            ->depth(0)
            ->in($this->workingDir)
        ;

        $webPrefix = $this->webPrefix.$this->path;

        return array_map(function($file) use ($webPrefix) {
                return new File($file, $webPrefix);
            },
            iterator_to_array($files->getIterator(), false)
        );
    }

    /**
     * @param $oldName
     * @param $newName
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function renameFile($oldName, $newName)
    {
        $oldFile = $this->workingDir.$oldName;
        $type = is_dir($oldFile) ? 'directory' : 'file';

        if ('file' === $type) {
            // don't let user change extension
            $newName = pathinfo($newName, PATHINFO_FILENAME).'.'.pathinfo($oldName, PATHINFO_EXTENSION);
        }

        if ($newName === $oldName) {
            throw new Exception(sprintf('You didn\'t specify a new name for "%s".', $newName));
        }

        $newFile = $this->workingDir.$newName;

        if (file_exists($newFile)) {
            throw new Exception(sprintf('The %s "%s" already exists.', $type, $newName));
        }

        try {
            $this->filesystem->rename($oldFile, $newFile);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error renaming %s "%s".  Check permissions.', $type, $oldName));
        }
    }

    /**
     * @param $filename
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function deleteFile($filename)
    {
        $file = $this->workingDir.$filename;
        $type = is_dir($file) ? 'directory' : 'file';

        try {
            $this->filesystem->remove($file);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error deleting %s "%s".  Check permissions.', $type, $filename));
        }
    }

    /**
     * @param $dirName
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function mkDir($dirName)
    {
        if (!$dirName) {
            throw new Exception('You didn\'t enter a directory name.');
        }

        $newDir = $this->workingDir.$dirName;

        if ($this->filesystem->exists($newDir)) {
            throw new Exception(sprintf('Failed to create directory "%s".  It already exists.', $dirName));
        }

        try {
            $this->filesystem->mkdir($newDir);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error creating directory "%s".  Check permissions.', $dirName));
        }
    }

    /**
     * @param UploadedFile $file
     *
     * @return null|string
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function uploadFile($file)
    {
        if (!$file instanceof UploadedFile) {
            throw new Exception('No file selected.');
        }

        $filename = $file->getClientOriginalName();

        if (file_exists($this->workingDir.$filename)) {
            throw new Exception(sprintf('File "%s" already exists.', $file->getClientOriginalName()));
        }

        try {
            $file->move($this->workingDir, $filename);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error uploading file "%s".  Check permissions.', $filename));
        }

        return $filename;
    }
}