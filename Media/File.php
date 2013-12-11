<?php

namespace Zenstruck\MediaBundle\Media;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class File
{
    const TYPE_FILE = 'file';
    const TYPE_DIR  = 'dir';

    protected $filename;
    protected $extension;
    protected $webPath;
    protected $image;
    protected $new;
    protected $size;
    protected $type;

    public function __construct(\SplFileInfo $file, $webPrefix)
    {
        $this->filename = $file->getFilename();
        $this->extension = $file->getExtension();
        $this->webPath = rtrim($webPrefix, '/').'/'.$this->filename;
        $this->image = in_array($this->extension, static::getImageExtensions());
        $this->new = (time() - $file->getCTime()) < 3600;
        $this->size = $this->getHumanFileSize($file->getSize());
        $this->type = $file->isDir() ? static::TYPE_DIR : static::TYPE_FILE;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function isImage()
    {
        return $this->image;
    }

    public function isNew()
    {
        return $this->new;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getWebPath()
    {
        return $this->webPath;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isDir()
    {
        return $this->type === static::TYPE_DIR;
    }

    public function isFile()
    {
        return $this->type === static::TYPE_FILE;
    }

    protected static function getImageExtensions()
    {
        return array('jpg', 'jpeg', 'gif', 'png');
    }

    /**
     * @see http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
     *
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    protected function getHumanFileSize($bytes, $decimals = 0)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), @$size[$factor]);
    }
}
