<?php

namespace Icinga\Module\Ondutymanager\Repository;

use DateTime;
use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Utils\PlanUtil;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;

class ScheduleRepository extends BaseLoggingRepository
{
    const MODULE_NAME = 'ondutymanager';

    private $teamId;
    private $startWeekday;
    private $startCycleTime;
    private $templateRepo;

    /**
     * ScheduleRepository constructor.
     * The parent constructor is called passing the module name as parameter.
     * This will allow the repository to be reachable by hooks.
     */
    public function __construct(int $teamId = null)
    {
        parent::__construct(self::MODULE_NAME);
        $this->teamId = $teamId;
        $teamRepo = new TeamRepository();
        $this->templateRepo = new TemplateRepository();
        $team = $teamRepo->findById($this->teamId);
        if ($team) {
            $this->startWeekday = $team->getStartWeekday();
            $this->startCycleTime = $team->getStartCycleTime();
        }
    }

    public function getSchedulesByDay($date)
    {
        $schedules = $this->findAllByFilters(["start_date" => $date, "team_id" => $this->teamId]);
        $this->sortDay($schedules);
        return $schedules;
    }

    public function getSchedulesOfCurrentCalendarWeek()
    {
        return $this->getSchedulesOfCalendarWeek(date('W'), date('Y'));
    }

    public function getSchedulesOfCalendarWeek($calendarWeek, $calendarYear)
    {
        // if the team is not set yet, don't retrieve the schedules and return an empty list
        if (!$this->teamId)
            return [];

        $startDateOfTeamWeek = PlanUtil::getStartDateOfTeamWeek($calendarWeek, $calendarYear, $this->teamId);
        $endDateOfTeamWeek = PlanUtil::getEndDateOfTeamWeek($calendarWeek, $calendarYear, $this->teamId);

        //New yer issue
        $NewYearCondition = intval(substr($endDateOfTeamWeek, 0, 4)) != intval(substr($startDateOfTeamWeek, 0, 4))
            || $calendarWeek == 1;

        //$schedules = $this->retrieveSchedulesOfCalendarWeek($calendarWeek, $calendarYear);
        if (!$NewYearCondition)
            $schedules = $this->retrieveSchedulesOfCalendarWeek($calendarWeek, $calendarYear);
        else
            $schedules = $this->retrieveSchedulesOfCalendarWeek($calendarWeek, $calendarYear, true);


        $days = $this->formatSchedulesToDays($schedules);

        $days = $this->filterDays($days, $startDateOfTeamWeek, $endDateOfTeamWeek);

        ksort($days);

        return $days;
    }

    private function retrieveSchedulesOfCalendarWeek($calendarWeek, $calendarYear, $newYearCondition = false)
    {
        // gets list of schedules of the wanted week and year
        $schedules = $this->getCurrentSchedulesOfCalendarWeek($calendarWeek, $calendarYear, $newYearCondition);

        // gets a list of schedules of the previous week
        $previousSchedules = $this->getSchedulesOfPreviousCalendarWeek($calendarWeek, $calendarYear);
        $schedules = array_merge($previousSchedules, $schedules);

        // gets a list of schedules of the previous week
        $nextSchedules = $this->getSchedulesOfNextCalendarWeek($calendarWeek, $calendarYear);
        $schedules = array_merge($schedules, $nextSchedules);

        return $schedules;
    }

    private function getCurrentSchedulesOfCalendarWeek($calendarWeek, $calendarYear, $newYearCondition = false)
    {
        $schedules = $this->findAllByFilters(["calendar_week" => $calendarWeek, "calendar_year" => $calendarYear, "YEAR(start_date)" => $calendarYear, "team_id" => $this->teamId], null, true);
        if ($newYearCondition) {
            $schedules = array_merge(
                $schedules,
                $this->findAllByFilters(["calendar_week" => PlanUtil::getNumberOfWeeksInYear($calendarYear - 1), "calendar_year" => $calendarYear - 1, "YEAR(start_date)" => $calendarYear-1, "team_id" => $this->teamId], null, true),
                $this->findAllByFilters(["calendar_week" => $calendarWeek, "calendar_year" => $calendarYear, "YEAR(start_date)" => $calendarYear + 1, "team_id" => $this->teamId], null, true)
            );
        }
        return $schedules;
    }

    private function getSchedulesOfPreviousCalendarWeek($calendarWeek, $calendarYear)
    {
        $calendarWeek -= 1;
        $start_year = $calendarYear;
        if ($calendarWeek == 0) {
            $calendarYear -= 1;
            $calendarWeek = PlanUtil::getNumberOfWeeksInYear($calendarYear);
        }

        $previousSchedules = $this->findAllByFilters(["calendar_week" => $calendarWeek, "calendar_year" => $calendarYear, "YEAR(start_date)" => $start_year, "team_id" => $this->teamId], null, true);

        return $previousSchedules;
    }

    private function getSchedulesOfNextCalendarWeek($calendarWeek, $calendarYear)
    {
        $calendarWeek += 1;
        $start_year = $calendarYear;
        if ($calendarWeek > PlanUtil::getNumberOfWeeksInYear($calendarYear)) {
            $calendarYear += 1;
            $calendarWeek = 1;
        }

        $nextSchedules = $this->findAllByFilters(["calendar_week" => $calendarWeek, "calendar_year" => $calendarYear, "YEAR(start_date)" => $start_year, "team_id" => $this->teamId], null, true);

        return $nextSchedules;
    }

    private function formatSchedulesToDays($schedules)
    {
        $ret = [];

        foreach ($schedules as $el) {
            $ret[$el->getStartDate()][] = $el;
        }


        foreach ($ret as $key => $day) {
            $this->sortDay($day);
            $ret[$key] = $day;
        }
        return $ret;
    }

    private function filterDays($days, $startDateOfTeamWeek, $endDateOfTeamWeek)
    {
        // remove the days which are before and after the week cycle
        $schedules = array_filter($days, function ($date) use ($startDateOfTeamWeek, $endDateOfTeamWeek) {
            return $date >= $startDateOfTeamWeek && $date <= $endDateOfTeamWeek;
        }, ARRAY_FILTER_USE_KEY);


        // remove the schedules from table, which are set before the start of the week cycle
        if (isset($schedules[$startDateOfTeamWeek])) {
            $newScheduleDay = [];
            foreach ($schedules[$startDateOfTeamWeek] as $el) {
                if (strtotime($el->getStartTime()) >= strtotime($this->startCycleTime))
                    $newScheduleDay[] = $el;
            }
            if (!empty($newScheduleDay))
                $schedules[$startDateOfTeamWeek] = $newScheduleDay;
            else
                unset($schedules[$startDateOfTeamWeek]);
        }

        // remove the schedules from table, which are set after the end of the week cycle
        if (isset($schedules[$endDateOfTeamWeek])) {
            $newScheduleDay = [];
            foreach ($schedules[$endDateOfTeamWeek] as $el) {
                if (strtotime($el->getStartTime()) < strtotime($this->startCycleTime))
                    $newScheduleDay[] = $el;
            }
            if (!empty($newScheduleDay))
                $schedules[$endDateOfTeamWeek] = $newScheduleDay;
            else
                unset($schedules[$endDateOfTeamWeek]);
        }

        $date = $startDateOfTeamWeek;

        // insert empty days if no schedule is set on a day of the week
        while ($date <= $endDateOfTeamWeek) {
            if (!array_key_exists($date, $schedules)) {
                $schedules[$date] = [];
            }
            $date = date('Y-m-d', strtotime($date . ' +1 day'));
        }

        return $schedules;
    }


    /**
     * sortDay sorts the days in the list from date and if the date is the same from time 
     * 
     * @param  mixed $day
     * @return void
     */
    private function sortDay(array &$day)
    {
        usort($day, function (ScheduleModel $a, ScheduleModel $b): int {
            return
                [$a->getStartDate(), strtotime($a->getStartTime())]
                <=>
                [$b->getStartDate(), strtotime($b->getStartTime())];
        });
    }

    /**
     * getColumns takes all schedules of the whole list as argument and
     * returns a distinct list of starttimes of the schedules,
     * which will then be used as the table header.
     *
     * @param  mixed $models which contains all the schedules of the whole week
     * @return void
     */
    public function getColumns(array $models)
    {
        $timeslots = [];

        $tmp = [];
        foreach ($models as $day)
            foreach ($day as $el)
                $tmp[] = $el;

        foreach ($tmp as $el) {
            $timestamp = strtotime($el->getStartTime());
            $timeslots[] = date('H:i:s', $timestamp);
        }

        // sort timeslots and make them distinct
        $timeslots = array_values(array_unique($timeslots));

        usort($timeslots, function ($a, $b): int {
            return strtotime($a) <=> strtotime($b);
        });

        return $timeslots;
    }

    protected function getAuditlogObjectNameColumn(&$paramsToAuditlogFunctions)
    {
        $paramsToAuditlogFunctions['objectName'] = "s";
    }

    public function getFirstYear()
    {
        return $this->getSingleYearByOrderBy(['start_date' => SORT_ASC]);
    }

    public function getLastYear()
    {
        return $this->getSingleYearByOrderBy(['start_date' => SORT_DESC]);
    }

    public function getSingleYearByOrderBy($orderBy)
    {
        $result = [];
        $dbResult = $this->dbSelect($this->prepareQuery('*', [], '', 1, null, $orderBy, true));
        if (!empty($dbResult)) {
            $result = $this->convertToSingleModelObject($dbResult[0]);
            $date = strtotime($result->getStartDate());
            $year = date("Y", $date);
        } else {
            $year = date("Y");
        }
        return $year;
    }

    public function getAllSchedulesOfWeekAndYearByTemplate($templateId, $calendarWeek, $calendarYear)
    {
        $ret = [];

        // if the team is not set yet, don't retrieve the schedules and return an empty list
        if (!$this->teamId)
            return [];

        $startDateOfTeamWeek = PlanUtil::getStartDateOfTeamWeek($calendarWeek, $calendarYear, $this->teamId);
        $endDateOfTeamWeek = PlanUtil::getEndDateOfTeamWeek($calendarWeek, $calendarYear, $this->teamId);

        $schedules = $this->retrieveSchedulesOfCalendarWeek($calendarWeek, $calendarYear);

        $schedules = array_filter($schedules, function ($schedule) use ($templateId) {
            return $schedule->getTemplateId() == $templateId;
        });

        $days = $this->formatSchedulesToDays($schedules);

        $days = $this->filterDays($days, $startDateOfTeamWeek, $endDateOfTeamWeek);

        foreach ($days as $day) {
            foreach ($day as $schedule) {
                $ret[] = $schedule;
            }
        }

        return $ret;
    }

    /**
     * Overrides function to return a list
     * Overrides function to return a list of models according to the implementation of BaseRepository and BaseModel,
     * but the data is sorted ascending by name.
     *
     * @param array $conditions
     * @param null $searchValue
     * @return array<BaseModel> || array
     * @throws ReflectionException
     */
    public function findAllByFilters(array $conditions = [], $searchValue = null, $applyPrepareQueryFilterModifier = true): array
    {
        $result = [];
        $orderByParams = $this->getOrderByParams();
        $dbResult = $this->dbSelect($this->prepareQuery('*', $conditions, $searchValue, 100, null, $orderByParams, $applyPrepareQueryFilterModifier));
        if (!empty($dbResult)) {
            $result = $this->convertToModelObjects($dbResult);
        }

        return $result;
    }

    /**
     * Overrides function to fetch the filter data order by name in ASC order.
     * @param null $value
     * @param null $direction
     * @return array
     */
    public function getOrderByParams($value = null, $direction = null)
    {
        return ['start_date' => SORT_DESC, 'start_time' => SORT_ASC];
    }

    /**
     * This function takes search value, params and return array of converted filters to db like query
     *
     * @param $searchValue
     * @param $searchParameters
     * @return array|null
     */
    protected function getSearchFilter($searchValue, $searchParameters)
    {
        $search = [];
        if (!empty($searchValue)) {
            foreach ($searchParameters as $key) {
                $search[] = [BaseModel::convertCodeNamesToSnakeCaseNames($key) . ' like ?' => '%' . $searchValue . '%'];
            }
        }

        return $search;
    }

    public function getColorCode($model)
    {
        $template = $this->templateRepo->findById($model->getTemplateId());
        return $template->getColorCode();
    }
}


function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}
