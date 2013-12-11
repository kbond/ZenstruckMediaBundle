<?php

namespace Zenstruck\MediaBundle\Media\Permission;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class BooleanPermissionProvider implements PermissionProviderInterface
{
    protected $return;

    public function __construct($return = true)
    {
        $this->return = $return;
    }

    public function canMkDir()
    {
        return $this->return;
    }

    public function canRenameFile()
    {
        return $this->return;
    }

    public function canRenameDir()
    {
        return $this->return;
    }

    public function canDeleteFile()
    {
        return $this->return;
    }

    public function canDeleteDir()
    {
        return $this->return;
    }

    public function canUploadFile()
    {
        return $this->return;
    }

    public function canReadFile()
    {
        return $this->return;
    }
}
