<?php

namespace Icinga\Module\Ondutymanager\Utils;

use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;

/**
 * ScheduleUtil contains some util functionalities for schedules
 */
class ScheduleUtil
{

    /**
     * toString prints all important information of a schedule
     * Used in confirm of insert for example
     *
     * @param  mixed $model
     * @return string
     */
    public static function toString(ScheduleModel $model): string
    {
        $format = "%s: %s, %s-%s, template: %s, calendarWeek: %d, calendarYear: %d";
        return sprintf($format, $model->getUserName(), $model->getStartDate(), $model->getStartTime(), $model->getEndTime() ? $model->getEndTime() : "\\", $model->getTemplate()->getName(), $model->getCalendarWeek(), $model->getCalendarYear());
    }

    /**
     * getScheduleAsAssociativeArray returns the model properties in an associative array
     * Used to generate the html schedule table
     *
     * @param  mixed $model
     * @return array
     */
    public static function getScheduleAsAssociativeArray(ScheduleModel $model): array
    {
        return [
                'username' => $model->getUsername(),
                'start date' => $model->getStartDate(),
                'start time' => $model->getStartTime(),
                'end date' => $model->getEndTime(),
                'end time' => $model->getEndTime(),
                'template' => $model->getTemplate()->getName(),
                'calendar week' => $model->getCalendarWeek(),
                'calendar year' => $model->getCalendarYear(),
        ];
    }

    /**
     * formatUserData takes a schedule object, takes the value of userName (all info is saved there) and sets the userId, userName and userPhoneNumber correctly.
     * It checks the value of the username, if is empty or if it contains all information and based on that sets the values.
     *
     * @param  mixed $modelObject
     * @return void
     */
    public static function formatUserData($modelObject)
    {
        $userName = $modelObject->getUserName();

        if ($userName != ScheduleModel::EMPTY_STRING && strpos($userName, ScheduleModel::USER_VALUES_DELIMITER) !== false) {
            $userValues = explode(ScheduleModel::USER_VALUES_DELIMITER, $userName);
            $modelObject->setUserId((int)$userValues[0] ? (int)$userValues[0] : null);
            $modelObject->setUserName(isset($userValues[1]) ? $userValues[1] : null);
            $modelObject->setUserPhoneNumber(isset($userValues[2]) ? $userValues[2] : null);
        } else if ($userName == ScheduleModel::EMPTY_STRING) {
            $modelObject->setUserId(null);
            $modelObject->setUserName(null);
            $modelObject->setUserPhoneNumber(null);
        }
    }
    
    /**
     * createModelByParams helper function to create you by giving parameters a schedulemodel with all the 
     * properties set that exist in the parameters
     *
     * @param  mixed $params
     * @return ScheduleModel
     */
    public static function createModelByParams($params): ScheduleModel
    {
        $modelObject = null;

        $constructor = array_flip(ScheduleModel::getConstructorParameterOrderedList());
        $constructor = array_fill_keys(array_keys($constructor), null);

        array_filter($params, function ($v, $k) use (&$constructor) {
            if (array_key_exists($k, $constructor))
                $constructor[$k] = is_numeric($v) ? (int)$v : (!empty($v) ? $v : null);
        }, ARRAY_FILTER_USE_BOTH);

        $repository = new ScheduleRepository($constructor["teamId"]);
        $modelObject = $repository->convertToSingleModelObject($constructor);

        ScheduleUtil::formatUserData($modelObject);

        return $modelObject;
    }
}
