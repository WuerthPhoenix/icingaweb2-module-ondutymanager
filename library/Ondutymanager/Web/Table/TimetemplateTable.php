<?php

namespace Icinga\Module\Ondutymanager\Web\Table;

use Icinga\Module\Neteye\Web\Table\BaseTable;
use Icinga\Module\Ondutymanager\Model\TimetemplateModel;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;

class TimetemplateTable extends BaseTable
{
    private $templateRepository;

    public function __construct($models)
    {
        $this->templateRepository = new TemplateRepository();

        $models = $this->sortModels($models);

        parent::__construct($models);
    }
    
    /**
     * sortModels sorts the timetemplates by its name, then the date and lastly by the time
     *
     * @param  mixed $models
     * @return void
     */
    protected function sortModels($models)
    {
         usort($models, function (TimetemplateModel $a, TimetemplateModel $b): int {
            $aName = $this->templateRepository->findById($a->getTemplateId())->getNameWithTeam();
            $bName = $this->templateRepository->findById($b->getTemplateId())->getNameWithTeam();
            $aDate = date('N', strtotime($a->getWeekday()));
            $bDate = date('N', strtotime($b->getWeekday()));

            return
                [$aName, $aDate, $a->getStartTime()]
                <=>
                [$bName, $bDate, $b->getStartTime()];
        });

        return $models;
    }

    protected function renderTemplateIdHeader()
    {
        return "Template";
    }

    protected function renderTemplateIdValue($value)
    {
        $model = $this->templateRepository->findById($value);
        $value = $model->getNameWithTeam();
        return $value;
    }
}
