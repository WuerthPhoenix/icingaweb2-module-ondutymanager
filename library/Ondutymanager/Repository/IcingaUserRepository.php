<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Ondutymanager\Utils\SettingsUtil;
use Icinga\Util\Translator;
use Icinga\Application\Config;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;

class IcingaUserRepository extends BaseLoggingRepository
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
        $condition = ['object_type' => 'object', 'disabled' => 'n'];
        return $this->findAllByFilters($condition);
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
    public function findAllUsersById($userIds)
    {
        $condition = ['id' => $userIds, 'object_type' => 'object', 'disabled' => 'n'];
        return $this->findAllByFilters($condition);
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
    public function findAllUsersByTeamId($teamId)
    {
        $usergroupId = (new TeamRepository())->findById($teamId)->getUsergroupId();
<<<<<<< HEAD

        $userIds = (new IcingaUsergroupUserRepository())->findUserIdsByUsergroupId($usergroupId);

=======
        $userIds = (new IcingaUsergroupUserRepository())->findUserIdsByUsergroupId($usergroupId);

	if (empty($userIds)) {
	    return [];
	}
>>>>>>> master
        return $this->findAllUsersById($userIds);
    }

    public function createUserOptions($users)
    {
        $options = [
            '' => Translator::translate('Not selected', 'neteye')
        ];

        $userVarRepo = new IcingaUserVarRepository();

        $settingsUtil = new SettingsUtil(self::Config());
        $settingValues = $settingsUtil->getCurrentConfiguration();

        foreach ($users as $user) {
            $uservars = $userVarRepo->findVarsByUserId($user->getId());
            if (!empty($uservars)) {
                $options[$user->getId() . ScheduleModel::USER_VALUES_DELIMITER . $uservars[SettingsUtil::ALIAS] . $settingValues[SettingsUtil::PHONE_NUMBER_SUFFIX] . ScheduleModel::USER_VALUES_DELIMITER . $uservars[SettingsUtil::PHONE_NUMBER]] = $uservars[SettingsUtil::ALIAS] . $settingValues[SettingsUtil::PHONE_NUMBER_SUFFIX];
                $options[$user->getId() . ScheduleModel::USER_VALUES_DELIMITER . $uservars[SettingsUtil::ALIAS] . $settingValues[SettingsUtil::MOBILE_PHONE_NUMBER_SUFFIX] . ScheduleModel::USER_VALUES_DELIMITER . $uservars[SettingsUtil::MOBILE_PHONE_NUMBER]] = $uservars[SettingsUtil::ALIAS] . $settingValues[SettingsUtil::MOBILE_PHONE_NUMBER_SUFFIX];
<<<<<<< HEAD
=======

>>>>>>> master
            }
        }

        $notSelectedString = Translator::translate('Not selected', 'neteye');
        uasort($options, function ($a, $b) use ($notSelectedString): int {
            return $a != $notSelectedString && $a > $b;
        });

        return $options;
    }

    public function getUserOptionsOfTeam($teamId)
    {
        $users = $this->findAllUsersByTeamId($teamId);

        return $this->createUserOptions($users);
    }

    public function getUserOptions()
    {
        $teams = (new TeamRepository())->findAll();

        $teamIds = [];
        foreach ($teams as $team) {
            $teamIds[] = $team->getId();
        }

        $users = [];
        foreach ($teamIds as $id) {
<<<<<<< HEAD
=======

>>>>>>> master
            $users = array_merge($users, $this->findAllUsersByTeamId($id));
        }

        return $this->createUserOptions($users);
    }

    /**
     * This method return a single model object according to the passed ID.
     *
     * @param $id
     * @return BaseModel || array
     * @throws ReflectionException
     */
    public function findById($id)
    {
        $result = NULL;

        $select = $this->prepareQuery('*', ['id' => $id, 'object_type' => 'object', 'disabled' => 'n']);

        $dbResult = $this->dbSelect($select);
        if (!empty($dbResult)) {
            $result = $this->convertToSingleModelObject($dbResult[0]);
        }
        return $result;
    }

    /**
     * @return Config
     */
    protected static function Config()
    {
        // @codingStandardsIgnoreEnd
        return Config::module('ondutymanager');
    }
}
