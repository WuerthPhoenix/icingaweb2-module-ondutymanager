<?php

namespace Icinga\Module\Ondutymanager\Web\Table;

use Icinga\Module\Neteye\Web\Table\BaseTable;

class IcingaTimeperiodRangeTable extends BaseTable
{
    public function __construct($models)
    {
        parent::__construct($models);
    }

    protected function getRowHref($model)
    {
    }

    protected function renderRangeKeyHeader()
    {
        return "Day";
    }
}
