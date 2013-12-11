<?php

namespace Zenstruck\MediaBundle\Media\Filter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface FilenameFilterInterface
{
    public function filter($filename);
}
