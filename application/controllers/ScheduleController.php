<?php

namespace Icinga\Module\Ondutymanager\Controllers;

use Icinga\Module\Ondutymanager\Utils\PermissionUtil;
use Icinga\Module\Neteye\Controllers\BaseModelController;
use Icinga\Module\Ondutymanager\Web\Form\ScheduleConfirmForm;
use Icinga\Module\Ondutymanager\Web\Form\ScheduleInsertForm;
use Icinga\Module\Ondutymanager\Web\Form\ScheduleHolidayForm;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;

/**
 * Class ScheduleController
 * @package Icinga\Module\Ondutymanager\Controllers
 * @related_object Schedule
 */
class ScheduleController extends BaseModelController
{
    public function init()
    {
        PermissionUtil::isAllowedForAdmin();
        parent::init();
    }

    public function insertAction()
    {
        $this->prepareAction('Insert');

        $request = $this->getRequest();

        $this->setViewTitle(ucfirst($request->getControllerName()));

        $this->content()->add(new ScheduleInsertForm());
    }

    public function holidayAction()
    {
        $this->prepareAction('Holiday');

        $request = $this->getRequest();

        $this->setViewTitle(ucfirst($request->getControllerName()));

        $this->content()->add(new ScheduleHolidayForm());
    }

    public function confirmAction()
    {
        $this->prepareAction('Confirm');

        $request = $this->getRequest();

        $this->setViewTitle(ucfirst($request->getControllerName()));

        $this->content()->add(new ScheduleConfirmForm());
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
