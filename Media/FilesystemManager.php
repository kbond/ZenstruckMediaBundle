<?php

namespace Zenstruck\MediaBundle\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\MediaBundle\Exception\Exception;
use Zenstruck\MediaBundle\Media\Alert\AlertProviderInterface;
use Zenstruck\MediaBundle\Media\Filter\FilenameFilterInterface;
use Zenstruck\MediaBundle\Media\Permission\PermissionProviderInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FilesystemManager
{
    const ALERT_ERROR   = 'error';
    const ALERT_SUCCESS = 'success';

    protected $name;
    protected $parameters;

    protected $alerts;
    protected $permissions;
    protected $allowedExtensions = array();

    /** @var FilenameFilterInterface[] */
    protected $filenameFilters = array();

    /** @var Filesystem */
    protected $filesystem;
    protected $files;

    public function __construct($name, array $parameters, Filesystem $filesystem,
        AlertProviderInterface $alerts, PermissionProviderInterface $permissions,
        $allowedExtensions = null)
    {
        $this->name = $name;
        $this->filesystem = $filesystem;
        $this->alerts = $alerts;
        $this->permissions = $permissions;
        $this->parameters = $parameters;
        $this->files = $this->filesystem->getFiles();

        if ($allowedExtensions) {
            $this->allowedExtensions = array_map('trim', explode(',', $allowedExtensions));
        }

        if (!$filesystem->isWritable()) {
            $this->alerts->add('This directory is not writable. Check permissions.', static::ALERT_ERROR);
        }
    }

    public function addFilenameFilter(FilenameFilterInterface $filter)
    {
        $this->filenameFilters[] = $filter;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getPath()
    {
        return $this->filesystem->getPath();
    }

    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    public function getParameter($key, $default = null, array $validOptions = array())
    {
        // use default if not set
        $value = array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;

        // if not a valid value, use default
        if (count($validOptions) && !in_array($value, $validOptions)) {
            $value = $default;
        }

        return $value;
    }

    public function getRequestParams(array $params = array())
    {
        $defaults = array_merge($this->parameters, array(
            'path' => $this->getPath(),
            'filesystem' => $this->name,
        ));

        return array_merge($defaults, $params);
    }

    public function getAncestry()
    {
        if ($this->getPath()) {
            return explode('/', $this->getPath());
        }

        return array();
    }

    public function getPreviousPath()
    {
        return join('/', array_slice($this->getAncestry(), 0, count($this->getAncestry()) - 1));
    }

    public function renameFile($oldName, $newName)
    {
        if (!$this->permissions->canRenameFile()) {
            $this->alerts->add('You do not have the required permission to rename files.', static::ALERT_ERROR);
            return;
        }

        $newName = $this->filterFilename($newName);

        try {
            $filename = $this->filesystem->renameFile($oldName, $newName);
        } catch (Exception $e) {
            $this->alerts->add($e->getMessage(), static::ALERT_ERROR);
            return;
        }

        $this->alerts->add(sprintf('File "%s" renamed to "%s".', $oldName, $filename), static::ALERT_SUCCESS);
    }

    public function renameDir($oldName, $newName)
    {
        if (!$this->permissions->canRenameDir()) {
            $this->alerts->add('You do not have the required permission to rename directories.', static::ALERT_ERROR);
            return;
        }

        $newName = $this->filterFilename($newName);

        try {
            $this->filesystem->renameFile($oldName, $newName);
        } catch (Exception $e) {
            $this->alerts->add($e->getMessage(), static::ALERT_ERROR);
            return;
        }

        $this->alerts->add(sprintf('Directory "%s" renamed to "%s".', $oldName, $newName), static::ALERT_SUCCESS);
    }

    public function deleteFile($filename)
    {
        if (!$this->permissions->canDeleteFile()) {
            $this->alerts->add('You do not have the required permission to delete files.', static::ALERT_ERROR);
            return;
        }

        try {
            $this->filesystem->deleteFile($filename);
        } catch (Exception $e) {
            $this->alerts->add($e->getMessage(), static::ALERT_ERROR);
            return;
        }

        $this->alerts->add(sprintf('File "%s" deleted.', $filename), static::ALERT_SUCCESS);
    }

    public function deleteDir($filename)
    {
        if (!$this->permissions->canDeleteDir()) {
            $this->alerts->add('You do not have the required permission to delete directories.', static::ALERT_ERROR);
            return;
        }

        try {
            $this->filesystem->deleteFile($filename);
        } catch (Exception $e) {
            $this->alerts->add($e->getMessage(), static::ALERT_ERROR);
            return;
        }

        $this->alerts->add(sprintf('Directory "%s" deleted.', $filename), static::ALERT_SUCCESS);
    }

    public function mkDir($dirName)
    {
        if (!$this->permissions->canMkDir()) {
            throw new Exception('You do not have the required permission to create directories.');
        }

        $dirName = $this->filterFilename($dirName);
        $this->filesystem->mkDir($dirName);

        return sprintf('Directory "%s" created.', $dirName);
    }

    public function uploadFile($file)
    {
        if (!$this->permissions->canUploadFile()) {
            $this->alerts->add('You do not have the required permission to upload files.', static::ALERT_ERROR);
            return;
        }

        if (!$file instanceof UploadedFile) {
            $this->alerts->add('No file selected.', static::ALERT_ERROR);
            return;
        }

        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if (count($this->allowedExtensions) && !in_array($extension, $this->allowedExtensions)) {
            $this->alerts->add(sprintf('The extension "%s" is not allowed. Valid extensions: "%s"', $extension, join(', ', $this->allowedExtensions)), static::ALERT_ERROR);
            return;
        }

        $filename = $this->filterFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        if ($extension) {
            $filename .= '.'.$extension;
        }

        try {
            $this->filesystem->uploadFile($file, $filename);
        } catch (Exception $e) {
            $this->alerts->add($e->getMessage(), static::ALERT_ERROR);
            return;
        }

        $this->alerts->add(sprintf('File "%s" uploaded.', $filename), static::ALERT_SUCCESS);
    }

    protected function filterFilename($filename)
    {
        foreach ($this->filenameFilters as $filter) {
            $filename = $filter->filter($filename);
        }

        return $filename;
    }
}