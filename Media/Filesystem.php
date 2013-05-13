<?php

namespace Zenstruck\MediaBundle\Media;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\MediaBundle\Exception\DirectoryNotFoundException;
use Zenstruck\MediaBundle\Exception\Exception;
use Zenstruck\MediaBundle\Media\Filter\FilenameFilterInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Filesystem
{
    protected $name;
    protected $path;
    protected $rootDir;
    protected $webPrefix;

    /** @var FilenameFilterInterface[] */
    protected $filenameFilters = array();

    protected $filesystem;
    protected $workingDir;
    protected $allowedExtensions = array();

    public function __construct($name, $path, $rootDir, $webPrefix, $allowedExtensions = null)
    {
        // check for .. - user is trying to access invalid directories
        if (preg_match('#\.\.#', $path)) {
            throw new DirectoryNotFoundException(sprintf('Invalid directory.'));
        }

        $this->name = $name;
        $this->path = trim($path, '/');

        $this->webPrefix = trim($webPrefix) === '/' ? '/' : sprintf('/%s/', trim($webPrefix, '/'));
        $this->rootDir = rtrim($rootDir, '\\/');

        $this->filesystem = new BaseFilesystem();
        $this->workingDir = rtrim($this->rootDir.'/'.$this->path, '/').'/';

        if ($allowedExtensions) {
            $this->allowedExtensions = array_map('trim', explode(',', $allowedExtensions));
        }

        if (!is_dir($this->workingDir)) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" not found.', $this->workingDir));
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function addFilenameFilter(FilenameFilterInterface $filter)
    {
        $this->filenameFilters[] = $filter;
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
     * @return string The new filename
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function rename($oldName, $newName)
    {
        if (!$oldName) {
            throw new Exception('You didn\'t specify an old file/directory name.');
        }

        if (!$newName) {
            throw new Exception('You didn\'t specify an new file/directory name.');
        }

        $oldFile = $this->workingDir.$oldName;

        if (!file_exists($oldFile)) {
            throw new Exception(sprintf('The file/directory "%s" does not exist.', $oldName));
        }

        $type = is_dir($oldFile) ? 'directory' : 'file';

        // run filters on filename
        $newName = $this->filterFilename(pathinfo($newName, PATHINFO_FILENAME));

        if ('file' === $type) {
            // don't let user change extension
            $newName = $newName.'.'.strtolower(pathinfo($oldName, PATHINFO_EXTENSION));
        }

        if ($newName === $oldName) {
            throw new Exception(sprintf('You didn\'t specify a new name for "%s".', $oldName));
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

        return sprintf('%s "%s" renamed to "%s".', ucfirst($type), $oldName, $newName);
    }

    /**
     * @param $filename
     *
     * @return string
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function delete($filename)
    {
        if (!$filename) {
            throw new Exception('No file/directory specified.');
        }

        $file = $this->workingDir.$filename;

        if (!file_exists($file)) {
            throw new Exception(sprintf('No file/directory named "%s".', $filename));
        }

        $type = is_dir($file) ? 'directory' : 'file';

        try {
            $this->filesystem->remove($file);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error deleting %s "%s".  Check permissions.', $type, $filename));
        }

        return sprintf('%s "%s" deleted.', ucfirst($type), $filename);
    }

    /**
     * @param $dirName
     *
     * @return string
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function mkdir($dirName)
    {
        if (!$dirName) {
            throw new Exception('You didn\'t enter a directory name.');
        }

        $dirName = $this->filterFilename($dirName);

        $newDir = $this->workingDir.$dirName;

        if ($this->filesystem->exists($newDir)) {
            throw new Exception(sprintf('Failed to create directory "%s".  It already exists.', $dirName));
        }

        try {
            $this->filesystem->mkdir($newDir);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error creating directory "%s".  Check permissions.', $dirName));
        }

        return sprintf('Directory "%s" created.', $dirName);
    }

    /**
     * @param UploadedFile $file
     *
     * @return string
     *
     * @throws \Zenstruck\MediaBundle\Exception\Exception
     */
    public function uploadFile(UploadedFile $file)
    {
        $filename = $this->filterFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if (count($this->allowedExtensions) && !in_array($extension, $this->allowedExtensions)) {
            throw new Exception(sprintf('The extension "%s" is not allowed. Valid extensions: "%s"', $extension, join(', ', $this->allowedExtensions)));
        }

        if ($extension) {
            $filename .= '.'.$extension;
        }

        if (file_exists($this->workingDir.$filename)) {
            throw new Exception(sprintf('File "%s" already exists.', $filename));
        }

        try {
            $file->move($this->workingDir, $filename);
        } catch (\Exception $e) {
            throw new Exception(sprintf('Error uploading file "%s".  Check permissions.', $filename));
        }

        return sprintf('File "%s" uploaded.', $filename);
    }

    protected function filterFilename($filename)
    {
        foreach ($this->filenameFilters as $filter) {
            $filename = $filter->filter($filename);
        }

        return $filename;
    }
}