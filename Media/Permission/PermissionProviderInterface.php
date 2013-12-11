<?php

namespace Zenstruck\MediaBundle\Media\Permission;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface PermissionProviderInterface
{
    /**
     * @return bool
     */
    public function canMkDir();

    /**
     * @return bool
     */
    public function canRenameFile();

    /**
     * @return bool
     */
    public function canRenameDir();

    /**
     * @return bool
     */
    public function canDeleteFile();

    /**
     * @return bool
     */
    public function canDeleteDir();

    /**
     * @return bool
     */
    public function canUploadFile();

    /**
     * @return bool
     */
    public function canReadFile();
}
