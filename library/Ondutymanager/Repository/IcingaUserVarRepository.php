<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use Icinga\Application\Config;
use Icinga\Module\Ondutymanager\Utils\SettingsUtil;
use Icinga\Util\Translator;

/**
 * IcingaUsergroupUserRepository this repository actually has only the defined method in this class working,
 * because the model is not defined correctly like the rules intend it (id is missing, because there is no such
 * property in the database table).
 * Therefore do not use methods which are not declared in this class, otherwise it will don't work as soon as
 * it needs the id for some operation.
 */
class IcingaUserVarRepository extends BaseLoggingRepository
{
    const MODULE_NAME = 'director';

    private $settingValues;

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
     * This method return a single model object according to the passed ID.
     *
     * @param $id
     * @return BaseModel || array
     * @throws ReflectionException
     */
    public function findVarsByUserId($id)
    {
        if ($this->settingValues == null) {
            $settingsUtil = new SettingsUtil($this->Config());
            $this->settingValues = $settingsUtil->getCurrentConfiguration();
        }

        // removes the suffix settings
        $varnameValues = array_filter($this->settingValues, function ($setting) {
            return strpos($setting, SettingsUtil::DB) === 0;
        }, ARRAY_FILTER_USE_KEY);

        $result = [];
        $dbResult = $this->dbSelect($this->prepareQuery('*', ['user_id' => $id, 'varname' => array_values($varnameValues)]));
        if (!empty($dbResult)) {
            foreach ($dbResult as $value) {
                foreach($varnameValues as $varNames => $varValues)
                    if($varValues == $value["varname"])
                        $result[$varNames] = $value["varvalue"];
            }
        }
        return $result;
    }

    /**
     * @return Config
     */
    protected function Config()
    {
        // @codingStandardsIgnoreEnd
        return Config::module('ondutymanager');
    }
}
