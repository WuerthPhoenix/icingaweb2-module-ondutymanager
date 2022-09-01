<?php

namespace Icinga\Module\Ondutymanager\Web\Table;

use Icinga\Module\Neteye\Web\Table\BaseTable;
use Icinga\Module\Ondutymanager\Repository\ColorRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;

class TemplateTable extends BaseTable
{
    public function __construct($models)
    {
        parent::__construct($models);
    }

    protected function renderColorIdHeader()
    {
        return "Color";
    }

    protected function renderColorIdValue($value)
    {
        $colorRepository = new ColorRepository();
        $model = $colorRepository->findById($value);
        $value = $model->getName();
        return $value;
    }

    protected function renderTeamIdHeader()
    {
        return "Team";
    }

    protected function renderTeamIdValue($value)
    {
        $teamRepository = new TeamRepository();
        $model = $teamRepository->findById($value);
        return $model->getName();
    }
}
