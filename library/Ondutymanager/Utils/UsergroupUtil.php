<?php

namespace Icinga\Module\Ondutymanager\Utils;

use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Ondutymanager\Database;
use Icinga\Module\Ondutymanager\Model\UsergroupModel;

class UsergroupUtil
{
    // public function findAll()
    // {
    //     $database = Database::create();

    //     $db = $database->getDbAdapter();
    //     $query = $db->select()->from("icinga_usergroup")->where("object_type = 'object' and disabled = 'n'")->order('object_name', 'ASC');

    //     $queryResult = $db->fetchAll($query);

    //     $result = array();
    //     foreach ($queryResult as $object) {
    //         $result[] = new UsergroupModel($object->id, $object->object_name);
    //     }

    //     return $result;
    // }

    // public static function getAllIcingaUsergroups()
    // {
    //     $usergroups = (new static)->findAll();

    //     return BaseFormUtil::convertModelToOptions(
    //         $usergroups,
    //         'name'
    //     );
    // }

    // public static function getIcingaUsergroupById($id)
    // {
    //     $usergroups = (new static)->findAll();
    //     foreach ($usergroups as $usergroup) {
    //         if ($usergroup->getId() == $id)
    //             return $usergroup->getName();
    //     }
    // }
}

// function console_log($data)
// {
//     echo '<script>';
//     echo 'console.log(' . json_encode($data) . ')';
//     echo '</script>';
// }
