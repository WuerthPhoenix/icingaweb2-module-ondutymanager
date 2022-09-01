<?php

namespace Icinga\Module\Ondutymanager\Controllers;

use Icinga\Module\Ondutymanager\Utils\PermissionUtil;
use Icinga\Module\Neteye\Controllers\BaseModelController;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;
use Icinga\Module\Ondutymanager\Repository\TimetemplateRepository;
use Icinga\Module\Ondutymanager\Web\Form\TimetemplateForm;

/**
 * Class TimetemplateController
 * @package Icinga\Module\Ondutymanager\Controllers
 * @related_object Timetemplate
 */
class TimetemplateController extends BaseModelController
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

        $templateRepository = new TemplateRepository();
        $templates = $templateRepository->findAllByFilters(["team_id" => $teamId]);
        $templateIds = [];
        if (!empty($templates)) {
            foreach ($templates as $template) {
                $templateIds[] = $template->getId();
            }
        }
        $timeTemplateRepository = new TimetemplateRepository();
        $timetemplates = [];
        if (!empty($templateIds)) {
            foreach ($templateIds as $id)
                $timetemplates = array_merge($timetemplates, $timeTemplateRepository->findAllByFilters(["template_id" => $id]));
        }
        $timetemplateIds = [];
        if (!empty($timetemplates)) {
            foreach ($timetemplates as $timetemplate) {
                $timetemplateIds[] = $timetemplate->getId();
            }
        }

        $filterConditions = ["id" => $timetemplateIds];
    }
}
