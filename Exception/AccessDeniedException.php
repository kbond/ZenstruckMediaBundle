<?php

namespace Zenstruck\MediaBundle\Exception;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AccessDeniedException extends Exception
{
    const STATUS_CODE = 403;
}