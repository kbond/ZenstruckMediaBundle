<?php

namespace Zenstruck\MediaBundle\Tests\Fixtures\Alert;

use Zenstruck\MediaBundle\Media\Alert\AlertProviderInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TestAlertProvider implements AlertProviderInterface
{
    public $message;
    public $type;

    public function add($message, $type)
    {
        $this->message = $message;
        $this->type = $type;
    }
}