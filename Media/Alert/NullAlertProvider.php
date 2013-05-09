<?php

namespace Zenstruck\MediaBundle\Media\Alert;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class NullAlertProvider implements AlertProviderInterface
{
    public function add($message, $type)
    {
    }
}