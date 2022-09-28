<?php

namespace Icinga\Module\Ondutymanager\Utils;

use Icinga\Module\Ondutymanager\Repository\TeamRepository;

class TeamUtil
{
    
    /**
     * getStartWeekdayOfTeam takes a team id as argument and returns the weekday,
     * at which the teams week cycle begins
     *
     * @param  mixed $teamId
     * @return string
     */
    public static function getStartWeekdayOfTeam($teamId): string
    {
        $team = (new TeamRepository)->findById($teamId);
        return $team->getStartWeekday();
    }
    
    /**
     * getStartCycleTimeOfTeam takes a team id as argument and returns the start time,
     * at which the teams week cycle begins
     *
     * @param  mixed $teamId
     * @return string
     */
    public static function getStartCycleTimeOfTeam($teamId): string
    {
        $team = (new TeamRepository)->findById($teamId);
        return $team->getStartCycleTime();
    }
}