<?php

namespace Zenstruck\MediaBundle\Exception;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Exception extends \RuntimeException
{
    const STATUS_CODE = 400;

    public function __construct($message = "", Exception $previous = null)
    {
        parent::__construct($message, static::STATUS_CODE, $previous);
    }
}
