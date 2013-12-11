<?php

namespace Zenstruck\MediaBundle\Media\Permission;

use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RolePermissionProvider implements PermissionProviderInterface
{
    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function canMkDir()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_MKDIR');
    }

    public function canRenameFile()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_RENAME_FILE');
    }

    public function canRenameDir()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_RENAME_DIR');
    }

    public function canDeleteFile()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_DELETE_FILE');
    }

    public function canDeleteDir()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_DELETE_DIR');
    }

    public function canUploadFile()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_UPLOAD');
    }

    public function canReadFile()
    {
        return $this->securityContext->isGranted('ROLE_MEDIA_READ');
    }
}
