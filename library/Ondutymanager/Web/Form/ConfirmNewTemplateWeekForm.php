<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Utils\PlanUtil;
use ipl\Html\Html;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;
use Icinga\Module\Ondutymanager\Repository\TimetemplateRepository;
use Icinga\Module\Ondutymanager\Utils\TeamUtil;
use Icinga\Util\Translator;

/**
 * ConfirmNewTemplateWeekForm class which creates a form to confirm if the user
 * wants to create a new onduty week by template.
 * If there are already elements in the week, you can not create a new week. 
 */
class ConfirmNewTemplateWeekForm extends ScheduleForm
{
    private $ok;
    private $calendarWeek;
    private $year;
    private $teamId;

    public function __construct($content)
    {
        $this->addElementLoader('Icinga\Module\Neteye\Web\Form\Element');

        // retrieves params calendarweek, year and the teamId and saves them
        $this->retrieveParams();

        $this->repository = new ScheduleRepository($this->teamId);

        $objects = $this->repository->getSchedulesOfCalendarWeek($this->calendarWeek, $this->year);

        $this->ok = true;

        // checks if the calendar week already contains schedules or not
        if (!is_null($objects))
            foreach ($objects as $el)
                if (!empty($el))
                    $this->ok = false;

        // if no schedules and every param is set, make nothing, otherwise dont let the user create a new week by template
        if ($this->ok && !empty($this->year) && !empty($this->calendarWeek) && !empty($this->teamId)) {
            $content->add(Html::tag('h1', [
                'class' => 'information'
            ], Translator::translate('Information', "ondutymanager")));
        } else {
            $this->ok = false;
            $content->add(Html::tag('h1', [
                'class' => 'information'
            ], Translator::translate('Error', "ondutymanager")));
            $content->add(Html::tag('p', [
                'class' => 'information'
            ], Translator::translate('Can not create new week by template: Schedules found in this calendar week and year or calendar week or year or team not set as parameter', "ondutymanager")));
        }

        parent::__construct();
    }

    /**
     * This function is override of DeleteBaseForm class function prepareAssemble, will use populates the delete form.
     */
    public function prepareAssemble()
    {
        if ($this->ok) {
            $confirmLabel = Html::tag('span', [], Translator::translate('Are you sure you want to create a new week by template?', 'ondutymanager'));
            $this->add($confirmLabel);

            $this->addSubmitAndCancelButton(
                Translator::translate('Create', 'ondutymanager'),
                Translator::translate('Cancel', 'neteye')
            );
        } else {
            $this->addSubmitButton(
                Translator::translate('Cancel', 'neteye')
            );
        }
    }


    /**
     * hasBeenSubmitted checks which button the user clicked.
     * If cancel, go back.
     * If create, then create new schedules for the week with the right template
     *
     * @return void
     */
    public function hasBeenSubmitted()
    {
        // writes the parameters in the url
        if ($this->hasBeenSent()) {

            if (!is_null($this->getSentValue(Translator::translate('Create', 'ondutymanager'))) && $this->teamId && $this->calendarWeek) {

                $templates = (new TemplateRepository())->findAllByFilters(["team_id" => $this->teamId]);

                $templateIds = [];
                foreach ($templates as $template) {
                    $templateIds[] = $template->getId();
                }

                $ttRepo = new TimetemplateRepository();
                
                $timetemplates = [];
                foreach ($templateIds as $id)
                    $timetemplates = array_merge($timetemplates, $ttRepo->findAllByFilters(["template_id" => $id]));

                if (!empty($timetemplates)) {
                    // foreach below will take every timetemplate, create the right date and time and create then a new schedule
                    // containing the calculated values
                    foreach ($timetemplates as $timetemplate) {
                        $startDate = PlanUtil::getStartDateOfTeamWeek($this->calendarWeek, $this->year, $this->teamId);

                        $numericStartWeekdayValue = PlanUtil::getNumericWeekdayValue($startDate);
                        $weekdayNumberInTeamWeek = PlanUtil::getWeekdayNumberInTeamWeek($this->teamId, $timetemplate->getWeekday());

                        $startCycleTime = TeamUtil::getStartCycleTimeOfTeam($this->teamId);
                        if ($weekdayNumberInTeamWeek == 0 && $timetemplate->getStartTime() < $startCycleTime)
                            $weekdayNumberInTeamWeek += 7;

                        $weekdayNumberInTeamWeek += $numericStartWeekdayValue;

                        $startDate = PlanUtil::getDateFormattedForTeamWeek($weekdayNumberInTeamWeek, $this->year, $this->calendarWeek);
                        // console_log($startDate);
                        $week = date("W", strtotime($startDate));
                        // console_log($week);
                        $year = date("o", strtotime($startDate));
                        // console_log($year);
                        $schedule = new ScheduleModel(null, $timetemplate->getTemplateId(), $startDate, $timetemplate->getStartTime(), null, $this->teamId, null, null, null, $week, $year, false);

                        $this->insertModelObject($schedule);
                    }
                }
            }

            // redirect to the planform of the just created week
            $baseFormUtil = new BaseFormUtil();
            $params = [
                'team' => $this->teamId,
                'year' => $this->year,
                'calendarWeek' => $this->calendarWeek
            ];

            $baseFormUtil->redirectToAction('index', $params);
        }
    }

    /**
     * retrieveParams retrieves the params teamId, calendarWeek and year in the url params
     * and saves them as member variables
     *
     * @return void
     */
    public function retrieveParams()
    {
        $this->teamId = (int)$this->getIcingaRequest()->getParam("team");
        $this->calendarWeek = (int)$this->getIcingaRequest()->getParam("calendarWeek");
        $this->year = (int)$this->getIcingaRequest()->getParam("year");
    }

    /**
     * Override: Below method will be used to fetch the full class namespace of the ScheduleModel using
     * the child FormClass who extends the BaseForm class.
     *
     * @param string $newClassType
     * @return string
     * @throws ReflectionException
     */
    protected function getClassName(string $newClassType = 'Model'): string
    {
        $className =  "Schedule" . $newClassType;
        $namespace = $this->getNamespaceName();
        return $namespace . $newClassType . '\\' . $className;
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
