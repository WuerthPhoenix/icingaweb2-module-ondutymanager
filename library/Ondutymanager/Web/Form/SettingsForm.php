<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Application\Config;
use Icinga\Module\Ondutymanager\Utils\SettingsUtil;
use Icinga\Util\Translator;
use Icinga\Web\Notification;

/**
 * SettingsForm Form which is used in the ConfigController and permits
 * the user to insert the names of the custom variables of the user
 * (alias, phone numbers, suffixes) and writes them in the config.ini
 */
class SettingsForm extends OndutymanagerForm
{
    /** @var Config */
    protected $config;
    protected $settingsUtil;

    public function __construct(Config $config)
    {
        $this->setIniConfig($config);
        $this->settingsUtil = new SettingsUtil($this->config);
        $this->populateForm();
        parent::__construct();
    }

    public function assemble()
    {
        $userAlias = $this->createElement('text', SettingsUtil::ALIAS, [
            'label' => Translator::translate('User\'s alias', 'ondutymanager'),
            'description' => Translator::translate(
                'The name of the custom variable field which contains the alias name of the user',
                'ondutymanager'
            ),
            'required' => 'required'
        ]);

        $userPhoneNumber = $this->createElement('text', SettingsUtil::PHONE_NUMBER, [
            'label' => Translator::translate('User\'s phone number', 'ondutymanager'),
            'description' => Translator::translate(
                'The name of the custom variable field which contains the phone number of the user',
                'ondutymanager'
            ),
            'required' => 'required'
        ]);

        $userPhoneNumberSuffix = $this->createElement('text', SettingsUtil::PHONE_NUMBER_SUFFIX, [
            'label' => Translator::translate('User\'s phone number suffix', 'ondutymanager'),
            'description' => Translator::translate(
                'Suffix to add to the user with phone number set',
                'ondutymanager'
            ),
            'required' => 'required'
        ]);

        $userMobilePhoneNumber = $this->createElement('text', SettingsUtil::MOBILE_PHONE_NUMBER, [
            'label' => Translator::translate('User\'s mobile phone number', 'ondutymanager'),
            'description' => Translator::translate(
                'The name of the custom variable field which contains the mobile phone number of the user',
                'ondutymanager'
            ),
            'required' => 'required'
        ]);

        $userMobilePhoneNumberSuffix = $this->createElement('text', SettingsUtil::MOBILE_PHONE_NUMBER_SUFFIX, [
            'label' => Translator::translate('User\'s mobile phone number suffix', 'ondutymanager'),
            'description' => Translator::translate(
                'Suffix to add to the user with mobile phone number set',
                'ondutymanager'
            ),
            'required' => 'required'
        ]);

        $userGroup = $this->createElement('text', SettingsUtil::USERGROUP, [
            'label' => Translator::translate('User\'s group', 'ondutymanager'),
            'description' => Translator::translate(
                'The name of the custom variable field which contains the usergroup of the user',
                'ondutymanager'
            ),
            'required' => 'required'
        ]);

        $this->addElements([$userAlias, $userPhoneNumber, $userPhoneNumberSuffix, $userMobilePhoneNumber, $userMobilePhoneNumberSuffix, $userGroup]);

        $this->addSubmitButton(Translator::translate('Save', 'ondutymanager'));
    }

    public function onSuccess()
    {
        $oldConfig = $this->settingsUtil->getCurrentConfiguration();

        $sections = $this->settingsUtil->configFormToArray($this->getValues());

        foreach ($sections as $section => $config) {
            $this->config->setSection($section, $config);
        }

        if ($this->save()) {
            $this->registerAuditlogAction($oldConfig);
            Notification::success(Translator::translate('New configuration has successfully been stored', 'geomap'));
        } else {
            return false;
        }
    }

    protected function setIniConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Persist the current configuration to disk
     *
     * If an error occurs the user is shown a view describing the issue and displaying the raw INI configuration.
     *
     * @return  bool                    Whether the configuration could be persisted
     */
    public function save()
    {
        try {
            $this->writeConfig($this->config);
        } catch (\Exception $e) {
            Notification::error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Write the configuration to disk
     *
     * @param   Config  $config
     */
    protected function writeConfig(Config $config)
    {
        $config->saveIni();
    }

    protected function populateForm()
    {
        $values = $this->settingsUtil->getCurrentConfiguration();
        $this->populate($values);
    }

    protected function registerAuditlogAction($oldConfig)
    {
        $configValues = $this->settingsUtil->getCurrentConfiguration();;
        $this->settingsUtil->registerAuditlogAction($configValues, $oldConfig);
    }
}
