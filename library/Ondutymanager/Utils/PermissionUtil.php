<?php

namespace Icinga\Module\Ondutymanager\Utils;

use Icinga\Module\Neteye\Utils\UserPermissionUtil;
use Icinga\Authentication\Auth;

/**
 * PermissionUtil class which offers functions to get the permissions of a user
 */
class PermissionUtil extends UserPermissionUtil
{
    const FULL_MODULE_ACCESS_PERMISSION = 'ondutymanager/*';
    const GENERAL_MODULE_ACCESS_PERMISSION = 'module/ondutymanager';

        
    /**
     * isAllowedForAdmin makes nothing if a user is allowed, otherwise throws an error that the user
     * does not have the permission to access the page
     *
     * @return void
     */
    public static function isAllowedForAdmin()
    {
        return (new self())->checkPermissions(Auth::getInstance(), [self::FULL_MODULE_ACCESS_PERMISSION]);
    }

    /**
     * Return a boolean containing if a user has at least full module access
     * @return bool
     * @throws ProgrammingError
     */
    public static function isOndutymanagerAdmin(): bool
    {
        $userPermissions = self::getUserPermissions();
        return (isset($userPermissions['*']) || isset($userPermissions[self::FULL_MODULE_ACCESS_PERMISSION]));
    }

    /**
     * Returns a boolean containing if a user has at least general module access
     * @return bool
     * @throws ProgrammingError
     */
    public static function hasOndutymanagerAccess(): bool
    {
        $userPermissions = self::getUserPermissions();
        return (self::isOndutymanagerAdmin() || isset($userPermissions[self::GENERAL_MODULE_ACCESS_PERMISSION]));
    }
}
