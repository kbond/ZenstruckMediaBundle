<?php

namespace Zenstruck\MediaBundle\Exception;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DirectoryNotFoundException extends Exception
{
    public function __construct($message = "", $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}