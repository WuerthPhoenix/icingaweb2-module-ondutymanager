<?php

namespace Icinga\Module\Ondutymanager\Utils;

use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Ondutymanager\Database;
use Icinga\Module\Ondutymanager\Model\UserModel;
use Icinga\Application\Icinga;

class UserUtil
{
     /**
     * @return mixed
     * @throws \Icinga\Exception\ProgrammingError
     */
    public static function getUser()
    {
        return Icinga::app()->getRequest()->getUser()->getLocalUsername();
    }

    public static function getUserRestrictions()
    {
        return Icinga::app()->getRequest()->getUser()->getRestrictions('ondutymanager/filter/teams');
    }

    public static function getUserRestrictionsForTeamBox()
    {
        $result = '';
        $restrictions = self::getUserRestrictions();
        if (!empty($restrictions)) {
            $result = explode(',', $restrictions[0]);
        }
        return $result;
    }
}

// function console_log($data)
// {
//     echo '<script>';
//     echo 'console.log(' . json_encode($data) . ')';
//     echo '</script>';
// }
