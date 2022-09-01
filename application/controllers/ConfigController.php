<?php


namespace Icinga\Module\Ondutymanager\Controllers;


use Icinga\Module\Ondutymanager\Web\Controller;
use Icinga\Module\Ondutymanager\Web\Form\SettingsForm;
use Icinga\Module\Ondutymanager\Web\Tabs\ConfigTab;
use Icinga\Util\Translator;

/**
 * ConfigController is placed under /neteye/ondutymanager/config
 * and contains the form to define the names of the custom variables
 * used for the user phone numbers and alias management 
 */
class ConfigController extends Controller
{
    public function indexAction()
    {
        $this->setViewTitle(Translator::translate('Ondutymanager Configuration', 'ondutymanager'));
        $this->controls()->add((new ConfigTab())->activate('config'));
        $this->content()->add(new SettingsForm($this->Config()));
    }

}