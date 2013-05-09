<?php

namespace Zenstruck\MediaBundle\Media\Alert;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SessionAlertProvider implements AlertProviderInterface
{
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function add($message, $type)
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}