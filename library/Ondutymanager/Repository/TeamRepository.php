<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use Icinga\Module\Ondutymanager\Utils\UserUtil;

class TeamRepository extends BaseLoggingRepository
{
    const MODULE_NAME = 'ondutymanager';

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
        $dbResult = $this->dbSelect($this->prepareQuery('*', $conditions, $searchValue, null, null, $orderByParams, $applyPrepareQueryFilterModifier));
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
        return ['name' => SORT_ASC];
    }

    public function findTeamIdsWithRestriction()
    {
        $teamIds = [];
        $where = $this->addFilterCondition();
        $teams = $this->findAllByFilters($where);
        if (!empty($teams)) {
            foreach ($teams as $team) {
                $teamIds[] = $team->getId();
            }
        }
        return  $teamIds;
    }
    protected function addFilterCondition($where = [])
    {
        $userFilters = UserUtil::getUserRestrictionsForTeamBox();
        if (!empty($userFilters)) {
            $where['name'] = $userFilters;
        }
        return $where;
    }
}
