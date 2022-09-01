<?php


namespace Icinga\Module\Ondutymanager\Controllers;

use Icinga\Module\Ondutymanager\Utils\PermissionUtil;
use Icinga\Module\Neteye\Controllers\BaseModelController;

/**
 * Class ColorController
 * @package Icinga\Module\Ondutymanager\Controllers
 * @related_object Color
 */
class ColorController extends BaseModelController
{
    public function init()
    {
        PermissionUtil::isAllowedForAdmin();
        parent::init();
    }
}
