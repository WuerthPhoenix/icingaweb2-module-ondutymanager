<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use Icinga\Module\Ondutymanager\Model\TemplateModel;

class TemplateRepository extends BaseLoggingRepository
{
    const MODULE_NAME = 'ondutymanager';

    /**
     * TemplateRepository constructor.
     * The parent constructor is called passing the module name as parameter.
     * This will allow the repository to be reachable by hooks.
     */
    public function __construct()
    {
        parent::__construct(self::MODULE_NAME);
    }

    protected function getAuditlogObjectNameColumn(&$paramsToAuditlogFunctions)
    {
        $paramsToAuditlogFunctions['objectName'] = "t";
    }

    /**
     * Overrides function to return a list
     * Overrides function to return a list of models according to the implementation of BaseRepository and BaseModel,
     * but the data is sorted ascending by name.
     *
     * @param array $conditions
     * @param null $searchValue
     * @return array<BaseModel> || array
     * @throws ReflectionException
     */
    public function findAllByFilters(array $conditions = [], $searchValue = null, $applyPrepareQueryFilterModifier = true): array
    {
        $result = [];
        $orderByParams = $this->getOrderByParams();
        $dbResult = $this->dbSelect($this->prepareQuery('*', $conditions, $searchValue, null, null, $orderByParams, $applyPrepareQueryFilterModifier));
        if (!empty($dbResult)) {
            $result = $this->convertToModelObjects($dbResult);
        }

        usort($result, function (TemplateModel $a, TemplateModel $b): int {
            return
                [$a->getTeamName(), $a->getName()]
                <=>
                [$b->getTeamName(), $b->getName()];
        });

        return $result;
    }
}
