<?php


namespace Icinga\Module\Ondutymanager\Web\Tabs;

use Icinga\Module\Geomap\Web\Components\Molecules\Tabs;
use Icinga\Module\Neteye\Web\Components\Organisms\TabLinks\SingleTab;
use Icinga\Util\Translator;

/**
 * ConfigTab creates the tab in the configuration controller of the module to have 
 * tabs to switch between
 */
class ConfigTab extends SingleTab
{
    public function __construct($actionName = '')
    {
        $this->add('module', [
            'label' => Translator::translate('Module: Ondutymanager', 'ondutymanager'),
            'url'   => 'config/module',
            'urlParams' => array('name' => 'ondutymanager'),
        ]);

        $this->genericAdd(
            'ondutymanager',
            'config',
            Translator::translate('Configuration', 'ondutymanager'),
            $actionName
        );
    }
}
