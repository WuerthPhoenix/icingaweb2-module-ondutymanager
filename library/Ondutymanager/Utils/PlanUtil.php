<?php

namespace Icinga\Module\Ondutymanager\Utils;

use DateTime;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;


/**
 * PlanUtil contains functions which are useful in displaying the table of the schedules
 * and in calculating the modules entities and its properties
 */
class PlanUtil
{

    /**
     * getWeeksInYear returns a array of numbers, which represent the weeks of a year
     * Used for the year select box in the PlanForm
     *
     * @param  mixed $year
     * @return array the week array
     */
    public static function getWeeksInYear($year): array
    {
        $n = self::getNumberOfWeeksInYear($year);

        $ret = [];
        for ($i = 1; $i <= $n; $i++) {
            $ret[$i] = $i;
        }
        return $ret;
    }

    /**
     * getNumberOfWeeksInYear return the number of weeks in a year
     * Checks if a year has 52 or 53 weeks
     *
     * @param  mixed $year
     * @return int the number of weeks
     */
    public static function getNumberOfWeeksInYear($year): int
    {
        $date = new DateTime();
        $date->setISODate($year, 53);
        return $date->format("W") === "53" ? 53 : 52;
    }

    /**
     * getWeekdayNumberInTeamWeek
     *
     * @param  mixed $teamId
     * @param  mixed $weekDay
     * @return int
     */
    public static function getWeekdayNumberInTeamWeek($teamId, $weekDay): int
    {
        // retrieve the day, at which a team begins their week
        $teamRepo = new TeamRepository();
        $team = $teamRepo->findById($teamId);
        $startWeekday = $team->getStartWeekday();

        // format the weekday from a string in a number in order that it can be added/subtracted from the startdate of a calendar week to
        // Exp.: 'Saturday' -> -2
        $numericStartWeekdayValue = self::getNumericWeekdayValue($startWeekday);

        $numericWeekdayValue = self::getNumericWeekdayValue($weekDay);

        $calculatedWeekDay = $numericWeekdayValue - $numericStartWeekdayValue;

        if ($calculatedWeekDay < 0)
            $calculatedWeekDay += 7;

        return $calculatedWeekDay;
    }

    /**
     * getStartDateOfTeamWeek returns the first date of a team week cycle.
     * Takes the week, year and team as argument and checks by the start weekday of a team
     * what the date of this weekday is
     *
     * @param  mixed $week
     * @param  mixed $year
     * @param  mixed $teamId
     * @return string startdate
     */
    public static function getStartDateOfTeamWeek($week, $year, $teamId): string
    {
        // retrieve the day, at which a team begins their week
        $teamRepo = new TeamRepository();
        $team = $teamRepo->findById($teamId);
        $startWeekday = $team->getStartWeekday();

        // format the weekday from a string in a number in order that it can be added/subtracted from the startdate of a calendar week to
        // Exp.: 'Saturday' -> -2

        $numericWeekdayValue = self::getNumericWeekdayValue($startWeekday);

        $dateFormatted = self::getDateFormattedForTeamWeek($numericWeekdayValue, $year, $week);

        return $dateFormatted;
    }

    /**
     * getEndDateOfTeamWeek calls the getStartDateOfTeamWeek function and adds 7 days,
     * to return the end of the team week cycle
     *
     * @param  mixed $week
     * @param  mixed $year
     * @param  mixed $teamId
     * @return string
     */
    public static function getEndDateOfTeamWeek($week, $year, $teamId)
    {
        $startDate = new DateTime(self::getStartDateOfTeamWeek($week, $year, $teamId));
        $startDate->modify('+7 days');
        return $startDate->format('Y-m-d');
    }


    /**
     * getNumericWeekdayValue calculates the value of a weekday to be added/substracted from a week.
     * Exp.: Thursday -> -4, Friday -> -3 ..., Wednesday -> 2
     *
     * @param  mixed $weekday
     * @return int
     */
    public static function getNumericWeekdayValue($weekday)
    {
        $numericWeekdayValue = - (abs(date('N', strtotime($weekday)) - 7) + 1);
        if ($numericWeekdayValue <= -5)
            $numericWeekdayValue += 7;

        return $numericWeekdayValue;
    }


    /**
     * getDateFormattedForTeamWeek returns the date formatted with an offset of days
     *
     * @param  mixed $startNumericWeekday
     * @param  mixed $year
     * @param  mixed $week
     * @return string
     */
    public static function getDateFormattedForTeamWeek($startNumericWeekday, $year, $week): string
    {
        $date = new DateTime();

        $date->setISODate($year, $week);

        $date->modify(sprintf("%+d", $startNumericWeekday) . ' day' . (abs($startNumericWeekday) > 1 ? 's' : ''));

        return $date->format('Y-m-d');
    }


    /**
     * getYears returns the years which should be shown in the select box of the year
     *
     * @return array
     */
    public static function getYears(): array
    {
        $repository = new ScheduleRepository();
        $firstYear = $repository->getFirstYear();
        $lastYear = $repository->getLastYear();
        $ret = [];
        for ($i =0; $firstYear + $i <= $lastYear + 1; $i++) {
            $ret[$firstYear + $i] = $firstYear + $i;
        }
        return $ret;
    }

    /**
     * getDayColumnByDate returns the date with the text weekday (used in the plantable)
     *
     * @param  mixed $date
     * @return string
     */
    public static function getDayColumnByDate($date): string
    {
        $dto = new DateTime($date);
        $ret = $dto->format('Y-m-d, l');
        return $ret;
    }
}
