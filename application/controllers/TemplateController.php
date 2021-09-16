<?php

namespace Icinga\Module\Ondutymanager\Controllers;

use Icinga\Module\Ondutymanager\Utils\PermissionUtil;
use Icinga\Module\Neteye\Controllers\BaseModelController;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;

/**
 * Class TemplateController
 * @package Icinga\Module\Ondutymanager\Controllers
 * @related_object Template
 */
class TemplateController extends BaseModelController
{
    public function init()
    {
        PermissionUtil::isAllowedForAdmin();
        parent::init();
    }

    /**
     * This method will get the filter condition set in the URL and can be overridden in the child class to get specific
     * filters
     * @param $filterConditions
     */
    public function getFilterConditionsFromUrl(&$filterConditions)
    {
        $teamRepository = new TeamRepository();
        $teamId = $teamRepository->findTeamIdsWithRestriction();
        $filterConditions = ["team_id" => $teamId];
    }
}
