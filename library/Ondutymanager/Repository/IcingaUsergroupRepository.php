<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Repository\BaseLoggingRepository;

class IcingaUsergroupRepository extends BaseLoggingRepository
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
     * The whole database set of rows is returned and the filtering is allowed.
     *
     * @param array $conditions
     * @param null $searchValue
     * @return array<BaseModel> || array
     * @throws ReflectionException
     */
    public function findAll($searchValue = null)
    {
        $condition = ["object_type" => "object", "disabled" => "n"];
        return $this->findAllByFilters($condition);
    }
}
