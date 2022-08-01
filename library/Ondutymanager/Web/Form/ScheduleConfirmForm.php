<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Utils\PlanUtil;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use Icinga\Module\Ondutymanager\Utils\TeamUtil;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Util\Translator;
use Icinga\Web\Notification;
use ipl\Html\Html;


class ScheduleConfirmForm extends ScheduleForm
{

    private $modelObject;

    private $error;

    private $schedules;

    private $endTime;

    private $previousSchedule;

    private $nextSchedules;

    private $toDelete;

    private $current;

    private $calendarWeek;

    private $calendarYear;

    private $teamId;

    public function __construct()
    {
        $this->addElementLoader('Icinga\Module\Neteye\Web\Form\Element');

        parent::__construct();
    }

    /**
     * This function is override of DeleteBaseForm class function prepareAssemble, will use populates the delete form.
     */
    public function prepareAssemble()
    {
        // retrieve all schedule params and print it to show infos of the new schedule
        try {
            $this->modelObject = ScheduleUtil::createModelByParams($this->getIcingaRequest()->getParams());
            $this->teamId = $this->modelObject->getTeamId();
            $this->calendarWeek = $this->modelObject->getCalendarWeek();
            $this->calendarYear = $this->modelObject->getCalendarYear();
            $header = ScheduleUtil::toString($this->modelObject);
        } catch (\Exception $exception) {
            $header = "Error while processing the data";
            console_log($exception->getMessage());
        }


        $title = Html::tag('h1', [], $header);
        $this->add($title);
        
        //print the schedule params inside a table
        $tableRowData = ScheduleUtil::getScheduleAsAssociativeArray($this->modelObject);
        $tableRows = [];
        
        foreach($tableRowData as $key => $value){
            $th = Html::tag('th', [], $key);
            $td = Html::tag('td', [], $value);
            $row = Html::tag('tr', [], [$th, $td]);
            array_push($tableRows, $row);
        }

        $scheduleTable = Html::tag('table', [], $tableRows);
        $this->add($scheduleTable);

        // processes how the new schedule would be inserted to be able to print information about it
        $information = $this->process();

        $p = Html::tag('p', [], $information);
        $this->add($p);

        $hiddenObjectIdElement = $this->createElement('hidden', 'id', [
            'required' => true,
            'value' => $this->id
        ]);
        $this->add($hiddenObjectIdElement);

        $insertLabel = Html::tag('span', [], Translator::translate('Are you sure you want to insert this new schedule?', 'ondutymanager'));
        $this->add($insertLabel);

        if ($this->error) {
            $this->addSubmitButton(
                Translator::translate('Back', 'ondutymanager')
            );
        } else {
            $this->addSubmitAndCancelButton(
                Translator::translate('Insert', 'ondutymanager'),
                Translator::translate('Cancel', 'neteye')
            );
        }
    }

    /**
     * This method work as a hook which will be used to pass the custom HTML element attributes,
     * while creating submit button. This function can be overwrite in the child class to add any specific attribute.
     *
     * @param $btn
     */
    protected function addSubmitButtonCustomAttribute(&$btn)
    {
        if ($this->error) {
            $btn = $this->createElement(
                'submit',
                'Back',
                [
                    'id' => 'delete' . $this->moduleName . 'Cancel',
                    'class' => 'button-no-label cancel_delete'
                ]
            );
        }
    }

    public function process()
    {
        $this->error = false;

        $this->repository = new ScheduleRepository($this->modelObject->getTeamId());

        $this->schedules = $this->repository->getSchedulesByDay($this->modelObject->getStartDate());

        $this->current = null;

        $this->previousSchedule = null;

        // calculates the schedule which is in front of the new one
        foreach ($this->schedules as $schedule) {
            if (strtotime($schedule->getStartTime()) <= strtotime($this->modelObject->getStartTime())) {
                $this->previousSchedule = $schedule;
            } else {
                break;
            }
        }

        // calculates all the following schedules after the new one
        $this->nextSchedules = array_filter($this->schedules, function ($obj) {
            return $this->modelObject->getStartTime() < $obj->getStartTime();
        });
        $this->nextSchedules = array_values($this->nextSchedules);

        $this->endTime = $this->modelObject->getEndTime();

        // looks if the new schedule is inside in the week, otherwise do not let the user insert it
        $this->error = $this->newScheduleOutsideOfWeekCycle();

        if ($this->error)
            return Notification::error(Translator::translate('Error: Schedule can not be inserted before or after the week cycle.', "ondutymanager"));

        // Inserts simply a new schedule without changing already existing ones
        if ((empty($this->nextSchedules) && empty($this->endTime)) || (is_null($this->previousSchedule) && strtotime($this->endTime) <= strtotime($this->nextSchedules[0]->getStartTime()))) {
            $this->previousSchedule = null;
            $this->toDelete = [];
            $this->current = null;
            return Translator::translate('New schedule will be inserted.', 'ondutymanager');
            // case that end time is set and that is either the last schedule of the day or not the last but it does not interfere with the next one
        } else if ((empty($this->nextSchedules) && !empty($this->endTime)) || (!empty($this->endTime) && strtotime($this->endTime) <= strtotime($this->nextSchedules[0]->getStartTime()))) {
            $startTime = $this->previousSchedule->getStartTime();

            $format = "New schedule will be inserted into existing schedule: %s - %s.";
            $notificationText = sprintf($format, $this->previousSchedule->getUserName(), $startTime);

            if (!array_key_exists(0, $this->nextSchedules) || strtotime($this->endTime) < strtotime($this->nextSchedules[0]->getStartTime())) {
                $this->previousSchedule->setId(null);
                $this->previousSchedule->setStartTime($this->endTime);
            } else {
                $this->previousSchedule = null;
            }

            $this->toDelete = [];
            $this->current = null;
            return $notificationText;
            // case that the endtime is empty -> delete all following schedules
        } else if (empty($this->endTime)) {
            $notificationText = Translator::translate('New schedule will be inserted, but following schedule(s) will be deleted: ', 'ondutymanager');
            $this->toDelete = [];
            foreach ($this->nextSchedules as $el) {
                $this->toDelete[] = $el;
                $notificationText .= $el->getUserName() . ": " . $el->getStartTime() . "; ";
            }

            $this->previousSchedule = null;
            $this->current = null;

            return $notificationText;

            // case that new elements endtime interferes with start time of next one => insert and move starttime of next one which has a greater start time to endtime of new
            // have to check if it overrides only one or if it cancels other schedules
        } else if (strtotime($this->endTime) > strtotime($this->nextSchedules[0]->getStartTime())) {
            $notificationText = Translator::translate('New schedule will be inserted', 'ondutymanager');
            $this->toDelete = [];
            if (array_key_exists(1, $this->nextSchedules) && strtotime($this->nextSchedules[1]->getStartTime()) <= strtotime($this->endTime))
                $notificationText .= ", but following schedule(s) which will be deleted: ";
            foreach ($this->nextSchedules as $key => $el) {
                if (strtotime($el->getStartTime()) > strtotime($this->endTime) || !array_key_exists($key + 1, $this->nextSchedules) || (array_key_exists($key + 1, $this->nextSchedules) && strtotime($this->nextSchedules[$key + 1]->getStartTime()) > strtotime($this->endTime))) {
                    if (strtotime($el->getStartTime()) != strtotime($this->endTime))
                        $current = clone $el;
                    else
                        $current = null;
                    break;
                } else {
                    $this->toDelete[] = clone $el;
                    $notificationText .= $el->getUserName() . ": " . $el->getStartTime() . "; ";
                }
            }
            if (!is_null($current)) {
                $notificationText .= " and following schedule will be modified: " . $current->getUserName() . ": " . $current->getStartTime() . " -> " . date('H:i:s', strtotime($this->endTime)) . ";";
                $this->current = clone $current;
                $this->current->setStartTime($this->endTime);
            }

            $this->previousSchedule = null;

            return $notificationText;
            // unknown case
        } else {
            $this->error = true;
            return Notification::error(Translator::translate('Error: can not process the data', "ondutymanager"));
        }
    }

    /**
     * newScheduleOutsideOfWeekCycle controls if the new object is outside the week, that means:
     * the date can not be less than the start date,
     * not greater than the end date,
     * if the date == first date is has to be after the start of the week time cycle
     * if the date == last date is has to be before the end of the week time cycle
     *
     * @return void
     */
    public function newScheduleOutsideOfWeekCycle()
    {
        $ret = false;

        $teamStartDate = PlanUtil::getStartDateOfTeamWeek($this->calendarWeek, $this->calendarYear, $this->teamId);
        $teamStartCyle = TeamUtil::getStartCycleTimeOfTeam($this->teamId);
        $teamEndDate = PlanUtil::getEndDateOfTeamWeek($this->calendarWeek, $this->calendarYear, $this->teamId);

        if (
            $this->modelObject->getStartDate() < $teamStartDate
            || $this->modelObject->getStartDate() == $teamStartDate && strtotime($this->modelObject->getStartTime()) < strtotime($teamStartCyle)
            || $this->modelObject->getStartDate() == $teamEndDate && strtotime($this->modelObject->getStartTime()) >= strtotime($teamStartCyle)
            || $this->modelObject->getStartDate() > $teamEndDate
        )
            $ret = true;

        return $ret;
    }

    /**
     * This functions is called if the Insert button was pressed and inserts the new 
     * schedule and changes all needed schedules. At the end, redirects to the insert form
     * from first
     */
    public function onSuccess()
    {
        // insert the new schedule in the database
        $this->modelObject->setEndTime(null);

        $this->insertModelObject($this->modelObject);

        if (!$this->error) {
            $this->executeOperations();
        }

        if (!$this->error) {
            Notification::success("Success inserting the new schedule");
            $params = [
                'startDate' => $this->modelObject->getStartDate(),
                'teamId' => $this->teamId
            ];
            $this->baseFormUtil->redirectToAction('insert', $params);
        }
    }

    /**
     * executeOperations inserts the schedule from first after the new one if needed,
     * deletes the ones which have to be deleted and updates the ones which have to be updated
     *
     * @return void
     */
    private function executeOperations()
    {
        if (!is_null($this->previousSchedule))
            $this->insertModelObject($this->previousSchedule);

        if (!empty($this->toDelete)) {
            foreach ($this->toDelete as $el) {
                $this->deleteModelObject($el);
            }
        }

        if (!is_null($this->current))
            $this->updateModelObject($this->current);
    }

    /**
     * Returns true if the Insert button was pressed
     * @return bool
     * @throws ProgrammingError
     */
    public function hasBeenSubmitted()
    {
        if ($this->hasBeenSent()) {
            $name = "Insert";
            return $this->getSentValue($name) === $this->getSubmitButton($name)->getButtonLabel();
        }
    }

    /**
     * renderStartDateProperty renders the start date form element like it is normal,
     * but sets as value the date which is passed argument
     *
     * @param  mixed $modelProperty
     * @param  mixed $model
     * @return BaseFormElement
     */
    protected function renderStartDateProperty(string $modelProperty, string $model): BaseFormElement
    {
        $startDate = $this->renderProperty($modelProperty, $model);

        $date = $this->getIcingaRequest()->getParam("startDate");
        $startDate->setValue($date);

        return $startDate;
    }


    /**
     * renderEndTimeProperty renders the endtime custom
     *
     * @param  mixed $modelProperty
     * @param  mixed $model
     * @return BaseFormElement
     */
    protected function renderEndTimeProperty(string $modelProperty, string $model): BaseFormElement
    {
        $createElementAttributes = [];
        try {
            $docValues = $model::getModelPropertyPhpDocValues($modelProperty);

            // fetch the required attribute
            $requiredAttr = false;
            if (array_key_exists('@cli_create_mandatory', $docValues)) {
                $requiredAttr = true;
            }
            $createElementAttributes['required'] = $requiredAttr;

            // fetch the element type i.e text|textarea|select
            $elementType = str_replace(array("\n", "\r"), '', trim($docValues['@form_input_type']));

            // fetch the select box options Form element attribute
            if ($elementType == 'select') {
                $this->prepareSelectElementOptionsAttribute($docValues, $modelProperty, $createElementAttributes);
            }

            // fetch the label attribute
            if (array_key_exists('@translate_label', $docValues)) {
                $createElementAttributes['label'] = Translator::translate($docValues['@translate_label'], $this->moduleName);
            }

            // fetch the description attribute
            if (array_key_exists('@translate_tooltip', $docValues)) {
                $createElementAttributes['description'] = Translator::translate($docValues['@translate_tooltip'], $this->moduleName);
            }
        } catch (\Exception $e) {
            throw new \Exception(
                sprintf(
                    'unable to create form element for the model property: %s',
                    $modelProperty
                )
            );
        }

        return $this->createElement($elementType, $model::getColumnName($modelProperty), $createElementAttributes);
    }
}

function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}
