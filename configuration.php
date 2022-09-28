<?php

$menuSection = $this->menuSection(N_('On duty Manager'), array(
    'url'      => '/neteye/ondutymanager/plan',
    'icon'     => 'bell-alt',
));



$this->provideRestriction(
    'ondutymanager/filter/teams',
    $this->translate(
        'Limit access to the filters of teams defined by this regex.'
    )
);

$this->provideConfigTab('config', array(
    'title' => 'Configuration',
    'label' => 'Configuration',
    'url' => 'config'
));

$auth = \Icinga\Authentication\Auth::getInstance();
$permission = (new \Icinga\Module\Ondutymanager\Utils\PermissionUtil($auth));
if ($permission->isOnDutyManagerAdmin()) {
    $menuSection->add(N_('Editor'))
        ->setUrl('ondutymanager/team')
        ->setPriority(10);
}
