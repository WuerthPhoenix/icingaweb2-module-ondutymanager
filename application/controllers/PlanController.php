<?php

namespace Icinga\Module\Ondutymanager\Controllers;

use Icinga\Module\Neteye\BaseController;
use Icinga\Util\Translator;
use Icinga\Module\Neteye\Web\Components\Organisms\TabLinks\SingleTab;
use Icinga\Module\Ondutymanager\Web\Form\ConfirmNewTemplateWeekForm;
use Icinga\Module\Ondutymanager\Web\Form\PlanForm;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;

/**
 * PlanController is the controller that contains the on duty table and form
 * It contains also the action to create a new Week by the templates
 */
class PlanController extends BaseController
{
    protected $request;

    public function indexAction()
    {
        $this->setAutorefreshInterval(self::DEFAULT_AUTOREFRESH_INTERVAL);

        $this->prepareAction('index');

        $request = $this->getRequest();

        $this->setViewTitle(ucfirst($request->getControllerName()));

        $this->content()->add(new PlanForm($this->content(), $request, $this->getModuleName() . '/' . $request->getControllerName() . '/createnewtemplateweek'));
    }

    public function createnewtemplateweekAction()
    {
        $this->prepareAction('confirm');

        $this->content()->add(new ConfirmNewTemplateWeekForm($this->content()));
    }

    private function prepareAction($actionName)
    {
        $request = $this->getRequest();

        $controllerName = $request->getControllerName();

        $addTab = new SingleTab(
            $this->getModuleName(),
            $controllerName,
            Translator::translate(ucfirst($controllerName), 'ondutymanager'),
            $actionName
        );

        $addTab->activate($actionName . $controllerName);
        $this->controls()->add($addTab);
    }

    /**
     * This method will get the filter condition set in the URL and can be overridden in the child class to get specific
     * filters
     * @param $filterConditions
     */
    public function getFilterConditionsFromUrl(&$filterConditions)
    {
        //$categoryRepository = new ScheduleRepository();
        $categoryRepository = new TeamRepository();
        $categoriesId = $categoryRepository->findTeamIdsWithRestriction();
        $filterConditions = ["team_id" => $categoriesId];
    }
}
