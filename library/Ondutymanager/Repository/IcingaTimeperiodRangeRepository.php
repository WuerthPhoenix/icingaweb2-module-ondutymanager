<?php

namespace Icinga\Module\Ondutymanager\Repository;

use DateTime;
use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Ondutymanager\Utils\SettingsUtil;
use Icinga\Util\Translator;
use Icinga\Application\Config;
use Icinga\Module\Ondutymanager\Model\IcingaTimeperiodRangeModel;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;

class IcingaTimeperiodRangeRepository extends BaseLoggingRepository
{
    const MODULE_NAME = 'director';

    /**
     * ScheduleRepository constructor.
     * The parent constructor is called passing the module name as parameter.
     * This will allow the repository to be reachable by hooks.
     */
    public function __construct()
    {
        parent::__construct(self::MODULE_NAME);
    }

    /**
     * This method return a list of models according to the implementation of BaseRepository and BaseModel.
     * It returns all timeperiod ranges in the director db of the choosen timeperiod
     *
     * @param null $searchValue
     * @return array<BaseModel> || array
     * @throws ReflectionException
     */
    public function findAllRangesByTimeperiodId($id)
    {
        $rows = $this->dbSelect($this->prepareQuery('*', ['timeperiod_id' => $id]));
        return $this->convertToModelObjects($rows);
    }


    /**
     * findRangesByTimeperiodId returns an array contraining all holidays between the given start and end date
     *
     * @param  mixed $startDate
     * @param  mixed $endDate
     * @param  mixed $id
     * @return array
     */
    public function findRangesByTimeperiodId($startDate, $endDate, $id): array
    {
        $ranges = $this->findAllRangesByTimeperiodId($id);

        $ranges = $this->formatRanges($ranges, $startDate, $endDate);

        $this->sortRanges($ranges);

        return array_values($ranges);
    }

    private function formatRanges($ranges, $startDate, $endDate): array
    {
        return array_filter(array_map(function ($range) use ($startDate, $endDate) {
            $date = DateTime::createFromFormat($format = "Y-m-d", $range->getRangeKey());
            if ($date && $date->format($format) === $date) {
                $range->setRangeKey(DateTime::createFromFormat($format = "Y-m-d, l", $range->getRangeKey()));
                if ($date >= $startDate && $date <= $endDate)
                    return $range;
            } else {
                $date = date('Y-m-d', strtotime(ucfirst($range->getRangeKey())));
                $range->setRangeKey(date('Y-m-d, l', strtotime(ucfirst($range->getRangeKey()))));
                if ($date >= $startDate && $date <= $endDate)
                    return $range;
            }
        }, $ranges));
    }

    private function sortRanges(array &$ranges)
    {
        usort($ranges, function (IcingaTimeperiodRangeModel $a, IcingaTimeperiodRangeModel $b): int {
            return $a->getRangeKey() <=> $b->getRangeKey();
        });
    }
}
