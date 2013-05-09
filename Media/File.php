<?php

namespace Zenstruck\MediaBundle\Media;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class File
{
    protected $file;
    protected $webPath;

    protected static $imageExtensions = array(
        'jpg', 'jpeg', 'gif', 'png'
    );

    public function __construct(\SplFileInfo $file, $webPrefix)
    {
        $this->file = $file;
        $this->webPath = rtrim($webPrefix, '/').'/'.$this->file->getFilename();

    }

    public function __call($method, $args)
    {
        if (!method_exists($this->file, $method)) {
            throw new \Exception(sprintf('Method "%s" does not exist for "%s"', $method, get_class($this->file)));
        }

        return call_user_func_array(array($this->file, $method), $args);
    }

    public function isImage()
    {
        return in_array($this->file->getExtension(), static::$imageExtensions);
    }

    public function isNew()
    {
        return (time() - $this->file->getCTime()) < 3600;
    }

    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * @ref http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
     *
     * @param int $decimals
     *
     * @return string
     */
    public function getHumanFileSize($decimals = 0)
    {
        $bytes = $this->file->getSize();

        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), @$size[$factor]);
    }
}