<?php

namespace Icinga\Module\Ondutymanager\Web\Table;

use Icinga\Module\Neteye\Web\Table\BaseTable;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;

/**
 * ScheduleTable prints a table of the schedule and changes some of
 * the headers / values because they are foreign key and need some 
 * formating when displaying
 */
class ScheduleTable extends BaseTable
{

    private $templateRepository;
    private $icingaUserRepository;
    private $teamRepository;

    public function __construct($models)
    {
        $this->templateRepository = new TemplateRepository();
        $this->icingaUserRepository = new IcingaUserRepository();
        $this->teamRepository = new TeamRepository();
        parent::__construct($models);
    }

    protected function renderTemplateIdHeader()
    {
        return "Template";
    }

    protected function renderTemplateIdValue($value)
    {
        $model = $this->templateRepository->findById($value);
        $value = $model->getName();
        return $value;
    }

    protected function renderUserIdHeader()
    {
        return "User";
    }

    protected function renderUserIdValue($value)
    {
        $user = $this->icingaUserRepository->findById($value);

        if (!$user)
            return ScheduleModel::EMPTY_STRING;

        return $user->getObjectName();
    }

    protected function renderUserNameHeader()
    {
        return "Username";
    }

    protected function renderUserNameValue($value)
    {
        return $value ? $value : ScheduleModel::EMPTY_STRING;
    }

    protected function renderUserPhoneNumberHeader()
    {
        return "Phone number";
    }

    protected function renderUserPhoneNumberValue($value)
    {
        return $value ? $value : ScheduleModel::EMPTY_STRING;
    }

    protected function renderTeamIdHeader()
    {
        return "Team";
    }

    protected function renderTeamIdValue($value)
    {
        $model = $this->teamRepository->findById($value);
        return $model->getName();
    }
}
