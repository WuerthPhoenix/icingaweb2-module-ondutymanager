<?php

namespace Icinga\Module\Ondutymanager\Web\Table;

use Icinga\Module\Director\Objects\IcingaTimePeriod;
use Icinga\Module\Neteye\Web\Table\BaseTable;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\IcingaTimeperiodRepository;
use Icinga\Module\Ondutymanager\Repository\IcingaUsergroupRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;
use Icinga\Util\Translator;

class TeamTable extends BaseTable
{
    public function __construct($models)
    {
        parent::__construct($models);
    }

    protected function renderUserGroupIdHeader()
    {
        return Translator::translate("Contactgroup", "ondutymanager");
    }

    protected function renderUserGroupIdValue($value)
    {
        $usergroup = (new IcingaUsergroupRepository)->findById($value);

        if (!$usergroup)
            return ScheduleModel::EMPTY_STRING;

        return $usergroup->getObjectName();
    }

    protected function renderHolidayTemplateIdHeader()
    {
        return Translator::translate("Holiday Template", "ondutymanager");
    }

    protected function renderHolidayTemplateIdValue($value)
    {
        $template = (new TemplateRepository)->findById($value);

        if (!$template)
            return ScheduleModel::EMPTY_STRING;

        return $template->getName();
    }

    protected function renderHolidayTimeperiodIdHeader()
    {
        return Translator::translate("Holiday Timeperiod", "ondutymanager");
    }

    protected function renderHolidayTimeperiodIdValue($value)
    {
        $template = (new IcingaTimeperiodRepository())->findById($value);

        if (!$template)
            return ScheduleModel::EMPTY_STRING;

        return $template->getObjectName();
    }
}
