<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Repository\BaseLoggingRepository;


/**
 * IcingaUsergroupUserRepository this repository actually has only the defined method in this class working,
 * because the model is not defined correctly like the rules intend it (id is missing, because there is no such
 * property in the database table).
 * Therefore do not use methods which are not declared in this class, otherwise it will don't work as soon as
 * it needs the id for some operation.
 */
class IcingaUsergroupUserRepository extends BaseLoggingRepository
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
        $condition = [];
        return $this->findAllByFilters($condition);
    }

    /**
     * This method return a single model object according to the passed ID.
     *
     * @param $id
     * @return BaseModel || array
     * @throws ReflectionException
     */
    public function findUserIdsByUsergroupId($id)
    {
        $result = [];
        $dbResult = $this->dbSelect($this->prepareQuery('*', ['usergroup_id' => $id]));
        if (!empty($dbResult)) {
            foreach ($dbResult as $value) {
                $result[] = $value["user_id"];
            }
        }
        return $result;
    }
}
