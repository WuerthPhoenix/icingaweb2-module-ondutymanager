<?php

namespace Icinga\Module\Ondutymanager\Web\Components\Organisms\TabLinks;

use Icinga\Module\Neteye\Web\Components\Organisms\TabLinks\BaseModuleTabs;
use Icinga\Util\Translator;

/**
 * OndutymanagerModuleTabs Class which creates the tabs shown in the editor
 * Needs to exist for the BaseModelController to work properly
 */
class OndutymanagerModuleTabs extends BaseModuleTabs
{
    public function getControllersAndDisplayNames(): array
    {
        return [
            'team' => Translator::translate('Team', 'ondutymanager'),
            'schedule' => Translator::translate('Schedule', 'ondutymanager'),
            'template' => Translator::translate('Template', 'ondutymanager'),
            'timetemplate' => Translator::translate('Timetemplate', 'ondutymanager'),
            'color' => Translator::translate('Color', 'ondutymanager'),
        ];
    }
}
