<?php


namespace Icinga\Module\Ondutymanager\Utils;


use Icinga\Application\Config;
use Icinga\Module\Neteye\Utils\BaseLoggingUtil;
use Icinga\Application\Icinga;

/**
 * SettingsUtil used for retrieving the values which are set under
 * /neteye/shared/icingaweb2/conf/modules/ondutymanager/config.ini
 * Consists of the values of the custom variables containing the user
 * information
 */
class SettingsUtil
{
    const CONFIG_SECTION = 'user_vars';

    const DB = 'db_';
    const ALIAS = self::DB . 'alias';
    const PHONE_NUMBER = self::DB . 'phone_number';
    const PHONE_NUMBER_SUFFIX = 'phone_number_suffix';
    const MOBILE_PHONE_NUMBER = self::DB . 'mobile_phone_number';
    const MOBILE_PHONE_NUMBER_SUFFIX = 'mobile_phone_number_suffix';
    const USERGROUP = self::DB . 'usergroup';

    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function registerAuditlogAction(array $config, array $savedConfig)
    {
        BaseLoggingUtil::sendModifyAuditLog(
            $this->getModuleName(),
            'module',
            'configuration',
            $this->getConfigUrl(),
            $this->configFormToArray($savedConfig),
            $this->configFormToArray($config),
            $this->getUser()
        );
    }

    public function configFormToArray(array $config = [])
    {
        $sections = [];

        foreach ($this->transformEmptyValuesToNull($config) as $section => $value) {
            $sections[self::CONFIG_SECTION][$section] = $value;
        }

        return $sections;
    }

    public function getCurrentConfiguration()
    {
        $values = [];
        if ($this->config->hasSection(self::CONFIG_SECTION)) {
            foreach ($this->config->getSection(self::CONFIG_SECTION)->toArray() as $field => $value) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * Transform all empty values of the given array to null
     *
     * @param   array   $values
     *
     * @return  array
     */
    protected function transformEmptyValuesToNull(array $values)
    {
        array_walk($values, function (&$v) {
            if ($v === '' || $v === false || $v === array()) {
                $v = null;
            }
        });

        return $values;
    }

    protected function getConfigUrl()
    {
        $urlHelper = new \Zend_Controller_Action_Helper_Url();
        return $urlHelper->direct('index', 'config');
    }

    /**
     * @return mixed
     * @throws \Icinga\Exception\ProgrammingError
     */
    protected function getModuleName()
    {
        return Icinga::app()->getRequest()->getModuleName();
    }

    /**
     * @return mixed
     * @throws \Icinga\Exception\ProgrammingError
     */
    protected function getUser()
    {
        return Icinga::app()->getRequest()->getUser()->getLocalUsername();
    }
}


// function console_log($data)
// {
//     echo '<script>';
//     echo 'console.log(' . json_encode($data) . ')';
//     echo '</script>';
// }
