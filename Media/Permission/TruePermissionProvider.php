<?php

namespace Zenstruck\MediaBundle\Media\Permission;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TruePermissionProvider implements PermissionProviderInterface
{
    public function canMkDir()
    {
        return true;
    }

    public function canRenameFile()
    {
        return true;
    }

    public function canRenameDir()
    {
        return true;
    }

    public function canDeleteFile()
    {
        return true;
    }

    public function canDeleteDir()
    {
        return true;
    }

    public function canUploadFile()
    {
        return true;
    }
}