<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Neteye\Html\Utils\Url;
use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\IcingaTimeperiodRangeRepository;
use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;
use Icinga\Module\Ondutymanager\Utils\PlanUtil;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use Icinga\Module\Ondutymanager\Utils\SessionUtil;
use Icinga\Module\Ondutymanager\Web\Components\Organisms\CreateNewTemplateWeekBar\CreateNewTemplateWeekBar;
use Icinga\Module\Ondutymanager\Web\Table\IcingaTimeperiodRangeTable;
use Icinga\Module\Ondutymanager\Web\Table\PlanTable;
use ipl\Html\Html;
use Icinga\Util\Translator;
use Icinga\Module\Ondutymanager\Utils\UserUtil;

use function Icinga\Module\Monitoredinterfaces\Repository\console_log as RepositoryConsole_log;
use function Icinga\Module\Ondutymanager\Web\Form\console_log as FormConsole_log;

class PlanForm extends OndutymanagerForm
{
    private $repository;
    private $hasUnsavedChanges;

    public function __construct($content, $request, $link)
    {
        parent::__construct();

        $this->addElementLoader('Icinga\Module\Neteye\Web\Form\Element');

        // next lines are parsing of the params
        $teamId = (int)$this->getIcingaRequest()->getParam("team");

        $this->repository = new ScheduleRepository($teamId);

        $calendarWeek = (int)$this->getIcingaRequest()->getParam("calendarWeek");
        if (!$calendarWeek)
            $calendarWeek = date('W');

        $year = (int)$this->getIcingaRequest()->getParam("year");
        if (!$year)
            $year = date('Y');


        // checks if in the request params there is at least one of the template select boxes set
        $this->hasUnsavedChanges = count(array_flip($this->getTemplatesFromParams($this->getIcingaRequest()->getParams()))) > 1;

        $this->addConfigurationSelects($teamId, $calendarWeek, $year);

        $objects = null;
        $columns = null;

        if ($teamId) {
            $objects = $this->repository->getSchedulesOfCalendarWeek($calendarWeek, $year);
            $columns = $this->repository->getColumns($objects);
        }

        $showTable = false;

        // checks if objects are not empty
        if (!is_null($objects))
            foreach ($objects as $el)
                if (!empty($el))
                    $showTable = true;


        $ids = SessionUtil::retrieveTemplates();
        SessionUtil::deleteTemplates();

        // show table and zentrale pflege form if there are schedules in week, otherwise show button to bring user to form
        // to create new empty week
        if ($showTable) {
            // iterates over all schedules and sets the user values set in the session if they are not empty 
            if (!empty($ids)) {
                foreach ($objects as $day) {
                    foreach ($day as $object) {
                        if (array_key_exists($object->getTemplateId(), $ids) && $ids[$object->getTemplateId()] != "") {
                            $object->setUserName($ids[$object->getTemplateId()]);
                            ScheduleUtil::formatUserData($object);
                        }
                    }
                }
            }

            $content->add(new PlanTable($objects, $columns, $calendarWeek, $year, $teamId));
            $this->addZentralePlege($teamId);
        } else if ($teamId) {
            $content->add(Html::tag('h1', [
                'class' => 'information'
            ], Translator::translate('Information', "ondutymanager")));
            $content->add(Html::tag('p', [
                'class' => 'information'
            ], Translator::translate('No schedules found in this calendar week and year', "ondutymanager")));
            $content->add(new CreateNewTemplateWeekBar($request, $link . '?year=' . $year
                . '&calendarWeek=' . $calendarWeek
                . '&team=' . $teamId));
        }
        if ($teamId)
            $this->addHolidayTable($calendarWeek, $year, $teamId);
    }

    /**
     * addConfigurationSelects calls functions to create the select boxes of the team, week and year
     *
     * @param  mixed $teamId
     * @param  mixed $calendarWeek
     * @param  mixed $year
     * @return void
     */
    protected function addConfigurationSelects($teamId, $calendarWeek, $year)
    {
        $container = Html::tag('div', ['class' => 'container']);

        $this->addTeamSelect($teamId, $container);

        if ($teamId) {
            $this->addCalendarWeekSelect($calendarWeek, $year, $container);
            $this->addYearSelect($year, $container);
        }

        $this->add($container);
    }

    /**
     * addTeamSelect creates team select box with options and adds it to container
     *
     * @param  mixed $teamId
     * @param  mixed $container
     * @return void
     */
    protected function addTeamSelect($teamId, $container)
    {
        $teamsRepo = new TeamRepository();
        //$teams = $teamsRepo->findAll();
        if(UserUtil::getUserRestrictions() == "" || UserUtil::getUserRestrictions() == "*" ){
            $teams = $teamsRepo->findAll();
        }else
        {
            $teamsIds = $teamsRepo->findTeamIdsWithRestriction();
            if(!empty($teamsIds))
                $teams = $teamsRepo->findAllByFilters(["id" =>$teamsIds]);
            else
                return null;           
        }

        $teamSelectBox = $this->createElement(
            'baseSelectElement',
            'team',
            [
                'label' => Html::tag('span', [
                    'class' => 'information'
                ], Translator::translate('Team', 'ondutymanager')),
                'options' => BaseFormUtil::convertModelToOptions($teams, 'name'),
                'class' => 'autosubmit'
            ]
        )->setValue($teamId ? $teamId : "")
            ->disableOption("");

        $colWidth = $teamId ? '1' : '3';

        $container
            ->add(Html::tag('div', ['class' => 'container col-' . $colWidth . '-3'])
                ->add($teamSelectBox));
    }

    /**
     * addCalendarWeekSelect creates calendar week select box with options and adds it to container
     *
     * @param  mixed $calendarWeek
     * @param  mixed $year
     * @param  mixed $container
     * @return void
     */
    protected function addCalendarWeekSelect($calendarWeek, $year, $container)
    {
        $weeks = PlanUtil::getWeeksInYear($year);

        $calendarWeekSelectBox = $this->createElement(
            'baseSelectElement',
            'calendarWeek',
            [
                'label' => Html::tag('span', [
                    'class' => 'information'
                ], Translator::translate('Week', 'ondutymanager')),
                'options' => $weeks,
                'class' => 'autosubmit'
            ]
        )->setValue($calendarWeek);

        $container
            ->add(Html::tag('div', ['class' => 'container col-1-3'])
                ->add($calendarWeekSelectBox));
    }

    /**
     * addYearSelect creates year select box with options and adds it to container
     *
     * @param  mixed $year
     * @param  mixed $container
     * @return void
     */
    protected function addYearSelect($year, $container)
    {
        $years = PlanUtil::getYears();

        $yearSelectBox = $this->createElement(
            'baseSelectElement',
            'year',
            [
                'label' => Html::tag('span', [
                    'class' => 'information'
                ], Translator::translate('Year', 'ondutymanager')),
                'options' => $years,
                'class' => 'autosubmit'
            ]
        )->setValue($year);

        $container
            ->add(Html::tag('div', ['class' => 'container col-1-3'])
                ->add($yearSelectBox));
    }

    /**
     * addZentralePlege creates one select box for every template with users as options
     * to be able to fill the table`s schedules user and adds it to container
     *
     * @param  mixed $teamId
     * @return void
     */
    protected function addZentralePlege($teamId)
    {
        // table lags because of this probably
        $users = (new IcingaUserRepository)->getUserOptionsOfTeam($teamId);

        $templateRepo = new TemplateRepository();
        $templates = $templateRepo->findAllByFilters(["team_id" => $teamId]);

        $container = Html::tag('div', ['class' => 'container']);

        $container->add(Html::tag('h1', [
            'class' => 'information icon-calendar'
        ], Translator::translate('Zentrale Pflege', 'ondutymanager')));

        foreach ($templates as $template) {
            $selectBox = $this->createElement(
                'baseSelectElement',
                'template_' . $template->getId(),
                [
                    'label' => Html::tag('span', [
                        'class' => 'information'
                    ], $template->getName()),
                    'options' => $users,
                    'style' => 'background: ' . $template->getColorCode(),
                    'class' => 'autosubmit'
                ]
            )->setValue($this->getIcingaRequest()->getParam('template_' . $template->getId()));
            $container
                ->add(Html::tag('div', ['class' => 'container col-1-3'])
                    ->add($selectBox));
        }
        $this->add($container);

        if ($this->hasUnsavedChanges) {
            $this->add(Html::tag('h3', [
                'class' => 'information',
                'style' => 'color: red'
            ], Translator::translate('There are unsaved changes', "ondutymanager")));
        }

        $disabled = $this->saveButtonIsDisabled();
        $this->addSubmitButton("Save", $disabled);
    }

    /**
     * addHolidayTable adds as last element of the view a table containing all the holidays of the team of the selected week
     * If no holiday is in the selected week, the table will not be displayed
     *
     * @param  mixed $calendarWeek
     * @param  mixed $year
     * @param  mixed $teamId
     * @return void
     */
    protected function addHolidayTable($calendarWeek, $year, $teamId)
    {
        $startDateOfTeamWeek = PlanUtil::getStartDateOfTeamWeek($calendarWeek, $year, $teamId);
        $endDateOfTeamWeek = PlanUtil::getEndDateOfTeamWeek($calendarWeek, $year, $teamId);

        // retrieve all ranges of the teams timeperiod id
        $icingaTimeperiodRepo = new IcingaTimeperiodRangeRepository();
        $timeperiodId = (new TeamRepository())->findById($teamId)->getHolidayTimeperiodId();
        $ranges = $icingaTimeperiodRepo->findRangesByTimeperiodId($startDateOfTeamWeek, $endDateOfTeamWeek, $timeperiodId);

        if (!empty($ranges)) {
            $container = Html::tag('div', ['class' => 'container']);

            $container->add(Html::tag('h1', [
                'class' => 'information icon-flash'
            ], Translator::translate('Holidays', 'ondutymanager')));

            $container
                ->add(Html::tag('div', ['class' => 'container col-1-3'])
                    ->add(new IcingaTimeperiodRangeTable($ranges)));

            $this->add($container);
        }
    }
    /**
     * saveButtonIsDisabled calculates if every schedule in the temporary table (table retrieved from db + users set in the select boxes)
     * has a user set and therefore later the submit button will be disabled or not
     *
     * @return bool
     */
    private function saveButtonIsDisabled(): bool
    {
        $currentYear = $this->getIcingaRequest()->getParam("year");
        $currentCalendarWeek = $this->getIcingaRequest()->getParam("calendarWeek");

        $ids = $this->getTemplatesFromParams($this->getIcingaRequest()->getParams());

        $weeklySchedules = [];

        return !$this->formatUserValuesOfWeeklySchedules($weeklySchedules, $ids, $currentCalendarWeek, $currentYear);
    }

    /**
     * hasBeenSubmitted controls how the form was send.
     * If send by changing one of the team, year, week select boxes, jump to the table of the set
     * team, week and year.
     * If fields in the 'Zentrale Pflege' were changed, update the table with the user set in the select boxes.
     * If send with the 'Save' button, take all template select boxes which values are not empty
     * and update the schedules in the database with the new users. 'Save' button only selectable, if every schedule
     * contains a user.
     *
     * @return void
     */
    public function hasBeenSubmitted()
    {
        $this->repository = new ScheduleRepository($this->getIcingaRequest()->getPost("team"));

        if ($this->hasBeenSent()) {

            $currentYear = $this->getIcingaRequest()->getParam("year");
            $currentCalendarWeek = $this->getIcingaRequest()->getParam("calendarWeek");

            // redirects to table with new configuration values (team, week or year) if they were changed
            if ($this->hasConfigurationValuesChanged()) {
                $this->redirectOnSuccess($currentYear, $currentCalendarWeek);
            } else {
                // validate the schedules with their new user values, write them in the session and eventually in the database if every schedule contains a user
                $ids = $this->getTemplatesFromParams($this->getSentValues());

                SessionUtil::storeTemplates($ids);

                $weeklySchedules = [];
                $hasEveryScheduleSet = $this->formatUserValuesOfWeeklySchedules($weeklySchedules, $ids, $currentCalendarWeek, $currentYear);

                if ($hasEveryScheduleSet && !is_null($this->getSentValue("Save"))) {
                    foreach ($weeklySchedules as $schedule) {
                        $this->repository->update($schedule);
                    }
                    $this->redirectOnSuccess($currentYear, $currentCalendarWeek);
                }
            }
        }
    }


    /**
     * getTemplatesFromParams takes params which among other things contains template ids and the user set in the corresponding template select box
     * (either post params or request params), extracts the template bound ones and returns their ids with the user values.
     *
     * @param  mixed $templates
     * @return array
     */
    private function getTemplatesFromParams($templates): array
    {
        $ret = [];

        // extracs from all the params only the ones containing template information
        $templates = array_filter(
            $templates,
            function ($key) {
                return strpos($key, 'template_') !== false;
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($templates as $key => $value) {
            $ret[str_replace('template_', '', $key)] = $value;
        }

        return $ret;
    }

    /**
     * formatUserValuesOfWeeklySchedules takes in the params $ids the ids and user values set in the 'Zentrale Pflege' select boxes
     * and sets every schedule's user depending on its template and the user set in the $ids value
     * If no select boxes were selected, take only the schedules of the db and check their user values
     *
     * @param  mixed $weeklySchedules reference of a empty schedules array, which will write in it all schedules with user values set correctly 
     * @param  mixed $ids template ids
     * @param  mixed $currentCalendarWeek
     * @param  mixed $currentYear
     * @return bool true if every schedule contains a user, otherwise false 
     */
    private function formatUserValuesOfWeeklySchedules(&$weeklySchedules, $ids, $currentCalendarWeek, $currentYear): bool
    {
        $ret = true;

        // case that none of the template select boxes are set with a user 
        if (empty($ids)) {
            $days = $this->repository->getSchedulesOfCalendarWeek($currentCalendarWeek, $currentYear);
            foreach ($days as $day)
                foreach ($day as $schedule)
                    if ($schedule->getUserName() == ScheduleModel::EMPTY_STRING)
                        $ret = false;
            return $ret;
        }

        foreach ($ids as $templateId => $values) {
            $schedules = $this->repository->getAllSchedulesOfWeekAndYearByTemplate($templateId, $currentCalendarWeek, $currentYear);

            foreach ($schedules as $schedule) {
                if (!empty($values)) {
                    $schedule->setUserName($values);
                    ScheduleUtil::formatUserData($schedule);
                }
                if ($schedule->getUserName() == ScheduleModel::EMPTY_STRING)
                    $ret = false;
            }
            $weeklySchedules = array_merge($weeklySchedules, $schedules);
        }

        return $ret;
    }

    /**
     * redirectOnSuccess redirects the page to the same page but with the year and week set with the new values in the url and select box
     *
     * @param  mixed $currentYear year thats currently set in the url yet
     * @param  mixed $currentCalendarWeek week thats currently set in the url yet
     * @return void
     */
    private function redirectOnSuccess($currentYear, $currentCalendarWeek)
    {
        $url = Url::fromPath("ondutymanager/plan");

        $team = $this->getIcingaRequest()->getPost("team");
        $url->setParam("team", $team);

        $newYear = $this->getIcingaRequest()->getPost("year");
        if (empty($newYear))
            $newYear = date('Y');
        $url->setParam("year", $newYear);

        // checks if the currently selected year and the new year are the same. otherwise put the week at current week to avoid misprintings
        $newCalendarWeek = $this->getIcingaRequest()->getPost("calendarWeek");

        if ($currentYear != $newYear && $currentCalendarWeek == 53 || is_null($newCalendarWeek))
            $url->setParam("calendarWeek", date('W'));
        else
            $url->setParam("calendarWeek", $newCalendarWeek);

        $this->getIcingaResponse()->redirectAndExit($url);
    }


    /**
     * hasConfigurationValuesChanged checks if either the team, week or year select box' value was changed
     *
     * @return bool If changed, return true, otherwise false
     */
    protected function hasConfigurationValuesChanged(): bool
    {
        return $this->getIcingaRequest()->isPost() && (is_null($this->getIcingaRequest()->getPost("team")) ||
            is_null($this->getIcingaRequest()->getPost("year")) || is_null($this->getIcingaRequest()->getPost("calendarWeek")) || $this->getIcingaRequest()->getPost("team") != $this->getIcingaRequest()->getParam("team")
            || $this->getIcingaRequest()->getPost("year") != $this->getIcingaRequest()->getParam("year")
            || $this->getIcingaRequest()->getPost("calendarWeek") != $this->getIcingaRequest()->getParam("calendarWeek"));
    }

    /**
     * This method will be used to validate, if user is allowed to access the object or not
     * in below mode during delete action.
     * This method is written in the BASE FORM, which is now overridden here to validate if the user has permissions
     * to edit the object or not.
     * @throws \Exception
     */
    // protected function validateUserAccessPermission()
    // {
    //     if (!empty($this->object)) {
    //         if (!$this->repository->userAccessValidationForFilterObject($this->id)) {
    //             throw new SecurityException('No permission for this filter');
    //         }
    //     }
    // }

}


function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}
