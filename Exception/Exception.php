<?php

namespace Zenstruck\MediaBundle\Exception;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Exception extends \Exception
{
    public function __construct($message = "", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}