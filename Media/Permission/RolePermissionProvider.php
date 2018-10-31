<?php

namespace Zenstruck\MediaBundle\Media\Permission;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RolePermissionProvider implements PermissionProviderInterface
{
    protected $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function canMkDir()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_MKDIR');
    }

    public function canRenameFile()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_RENAME_FILE');
    }

    public function canRenameDir()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_RENAME_DIR');
    }

    public function canDeleteFile()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_DELETE_FILE');
    }

    public function canDeleteDir()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_DELETE_DIR');
    }

    public function canUploadFile()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_UPLOAD');
    }

    public function canReadFile()
    {
        return $this->authChecker->isGranted('ROLE_MEDIA_READ');
    }
}
