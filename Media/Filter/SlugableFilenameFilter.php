<?php

namespace Zenstruck\MediaBundle\Media\Filter;

use Cocur\Slugify\Slugify;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SlugableFilenameFilter implements FilenameFilterInterface
{
    protected $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function filter($filename)
    {
        return $this->slugify->slugify($filename);
    }
}