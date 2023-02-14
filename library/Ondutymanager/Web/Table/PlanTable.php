<?php

namespace Icinga\Module\Ondutymanager\Web\Table;

use Icinga\Application\Icinga;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Utils\PlanUtil;
use ipl\Html\Html;
use \ipl\Html\Table;
use gipfl\IcingaWeb2\Icon;
use gipfl\IcingaWeb2\Link;
use Icinga\Module\Ondutymanager\Utils\TeamUtil;

/**
 * PlanTable Very custom table
 * Table which is printed in the PlanController/PlanForm and displays
 * the whole week. The rows contain the days and the columns the different
 * distinct times. Every table cell is a contact which is on duty for the
 * specific time of the day
 */
class PlanTable extends Table
{
    protected $moduleName;
    protected $controllerName;
    private $scheduleRepo;
    private $columns;
    private $calendarWeek;
    private $calendarYear;
    private $startDate;
    private $endDate;
    private $startCycleTime;
    private $teamId;

    /**
     * __construct takes all needed information and prints the table
     *
     * @param  mixed $days array containing days with schedules
     * @param  mixed $columns the distinct times of all the schedules of a week to use as columns
     * @param  mixed $calendarWeek (used for calculations)
     * @param  mixed $calendarYear (used for calculations)
     * @param  mixed $teamId
     * @param  mixed $attributes
     * @return void
     */
    public function __construct($days, $columns, $calendarWeek, $calendarYear, $teamId, array $attributes = ['class' => 'layers-table common-table'])
    {
        $this->moduleName = Icinga::app()->getRequest()->getModuleName();
        $this->controllerName = Icinga::app()->getRequest()->getControllerName();

        if (!empty($days)) {
            $this->scheduleRepo = new ScheduleRepository();

            $this->columns = $columns;
            $this->calendarWeek = $calendarWeek;
            $this->calendarYear = $calendarYear;
            $this->teamId = $teamId;

            $this->startDate = PlanUtil::getStartDateOfTeamWeek($this->calendarWeek, $this->calendarYear, $this->teamId);
            $this->endDate = PlanUtil::getEndDateOfTeamWeek($this->calendarWeek, $this->calendarYear, $this->teamId);
            $this->startCycleTime = TeamUtil::getStartCycleTimeOfTeam($this->teamId);

            $this->setAttributes($attributes);
            $this->createTable($days, $columns);
        }
    }

    /**
     * This function will create table for derived class
     * @param $days
     */
    public function createTable($days, $columns)
    {
        $tr = Html::tag('tr', []);
        $headers = $this->getHeaders($columns);
        foreach ($headers as $header) {
            $tr->add($header);
        }
        $this->add($tr);
        $this->populate($days);
    }


    /**
     * This function will get headers for table of derived class
     * @param array $headers
     * @return array
     */
    protected function getHeaders(array $headers)
    {
        $result = [];

        $result[] = Html::tag('th', [], "Day");

        foreach ($headers as $header) {
            $result[] = Html::tag('th', [], $header);
        }

        $this->addAdditionalColumnHeaders($result);

        return $result;
    }


    /**
     * This function will populate records in table of derived class
     * @param array $models
     */
    protected function populate(array $days)
    {
        foreach ($days as $date => $models) {
            $this->addRow($date, $models);
        }
    }

    /**
     * This function will add records in table of derived class.
     * @param $model
     */
    protected function addRow($date, $models)
    {
        $rowOptions = [
            'data-base-target' => '_next',
        ];

        $tr = Html::tag(
            'tr',
            $rowOptions
        );

        // adds the first column containing the date and weekday
        $td = Html::tag('td');
        $day = PlanUtil::getDayColumnByDate($date);
        $td->add($day);
        $tr->add($td);

        $colcounter = 0;

        // if the day does not contain any schedules it fills the table columns of the day with empty table cell,
        // otherwise fill it with its schedules 
        if (empty($models)) {
            while ($colcounter != count($this->columns)) {
                $tr->add(Html::tag('td'));
                $colcounter++;
            }
        } else {
            foreach ($models as $key => $model) {
                // calculates for how many columns a schedule/user should be inserted in a row
                while (($key == array_key_last($models) || $this->columns[$colcounter] < $models[$key + 1]->getStartTime()) && $colcounter <= array_key_last($this->columns)) {
                    // if the model is in the right column, print it, otherwise print a empty not clickable column
                    if (
                        $model->getStartDate() == $this->startDate && $this->columns[$colcounter] < $this->startCycleTime ||
                        $model->getStartDate() == $this->endDate && $this->columns[$colcounter] >= $this->startCycleTime ||
                        $model->getStartTime() > $this->columns[$colcounter]
                    ) {
                        $td = Html::tag('td');
                    } else {
                        $td = $this->addCol($model);
                    }
                    $tr->add($td);
                    $colcounter++;
                }
            }
        }

        $this->addAdditionalColumns($tr, $date);

        $this->add($tr);
    }

    /**
     * This function will add records in table of derived class with edit link on col click.
     * @param $model
     */
    protected function addCol($model)
    {
        $colorCode = $this->scheduleRepo->getColorCode($model);

        $colOptions = [
            'data-base-target' => '_next',
            'style' => 'background-color: ' . $colorCode
        ];

        $td = Html::tag(
            'td',
            $colOptions
        );

        $link = Html::tag('a', ['href' => $this->getColHref($model), 'data-base-target' => '_next', 'style' => 'color:black'], $this->renderValue($model));

        $td->add($link);

        return $td;
    }

    /**
     * This function will render value in table
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function renderValue($model)
    {
        return $model->getUserName();
    }

    /**
     * This function will add additional columns in table, will override in derived class.
     * The additional column is the plus icon that gives you the ability to jump to the insert
     * form of a new schedule into this day
     * @param $tr
     * @param $model
     */
    protected function addAdditionalColumns(&$tr, $day)
    {
        $td = Html::tag('td');
        $params = [
            'startDate' => $day,
            'teamId' => $this->teamId,
            'calendarWeek' => $this->calendarWeek,
            'calendarYear' => $this->calendarYear
        ];

        // adds a add button to insert a schedule in the day of the row
        $link = new Link(new Icon('plus'), "ondutymanager/schedule/insert", $params,['title'=>'insert a schedule in the day of the row']);
        $td->add($link);

        // adds a holiday button to set the whole day as the teams holiday template,
        // but the first and last day of the teams week
        if ($day != $this->startDate && $day != $this->endDate) {
            $link = new Link(new Icon('check'), "ondutymanager/schedule/holiday", $params,['title'=>'set the whole day as the teams holiday template']);
            $td->add($link);
        }
        
        $tr->add($td);
    }

    /**
     * This function will add additional columns headers in table, will override in derived class
     * Adds a tempty column Header for the insert button column
     * @param $columnHeaders
     */
    protected function addAdditionalColumnHeaders(&$columnHeaders)
    {
        // To implement in classes that inherit this one to add additional columns
        $columnHeaders[] = Html::tag('th', [], "");
    }

    // Returns the link to the schedule edit form
    protected function getColHref($model)
    {
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $startDate = $model->getStartDate();
        $templateId = $model->getTemplateId();

        // return '/neteye/' . $this->moduleName . '/schedule/edit?id=' . $userId;
        return '/neteye/' . $this->moduleName . '/schedule/customedit?id=' . $id. '&start_date='. $startDate. '&team_id='.$this->teamId. '&start_time='. $startTime. '&template_id='. $templateId;
    }
}
