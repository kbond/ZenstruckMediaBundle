<?php

namespace Zenstruck\MediaBundle\Media\Filter;

use Cocur\Slugify\Slugify;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SlugifyFilenameFilter implements FilenameFilterInterface
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
