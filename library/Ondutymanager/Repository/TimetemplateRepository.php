<?php

namespace Icinga\Module\Ondutymanager\Repository;

use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Neteye\Repository\BaseLoggingRepository;
use ipl\Sql\Select;

class TimetemplateRepository extends BaseLoggingRepository
{

    private $searchValue;
    private $useAndInWhere;

    const MODULE_NAME = 'ondutymanager';

    /**
     * ScheduleRepository constructor.
     * The parent constructor is called passing the module name as parameter.
     * This will allow the repository to be reachable by hooks.
     */
    public function __construct()
    {
        $this->useAndInWhere = true;
        parent::__construct(self::MODULE_NAME);
    }

    /**
     * This function will return Select query object with given column,filters,search,limit,offset and order by
     *
     * @param $columns
     * @param array $filters
     * @param string $searchValue
     * @param null $limit
     * @param null $offset
     * @param array $orderBy
     * @param bool $applyFilterModifier
     * @return Select
     */
    protected function prepareQuery(
        $columns,
        $filters = [],
        $searchValue = '',
        $limit = null,
        $offset = null,
        $orderBy = [],
        $applyFilterModifiers = true,
        $renderedFilter = null
    ) {
        $selectAll = new Select();
        $selectAll->columns($columns)->from($this->getTable());

        $this->searchValue = $searchValue;

        $where = $this->getFiltersCondition($filters);

        if ($applyFilterModifiers) {
            $this->modifyFiltersCondition($filters, $where);
        }

        if (!empty($renderedFilter)) {
            $selectAll->where($renderedFilter);
        }

        if (!is_null($where)) {
            if ($this->useAndInWhere)
                $selectAll->orWhere($where);
            else
                $selectAll->orWhere($where);
        }

        if ($limit !== null) {
            $selectAll->limit($limit);
        }

        if ($offset !== null) {
            $selectAll->offset($offset);
        }

        if (!empty($orderBy)) {
            $selectAll->orderBy($orderBy);
        }

        return $selectAll;
    }

    /**
     * This method is used to modify the filters passed to the query builder
     *
     * @param $filters
     * @param $where
     * @return void
     */
    protected function modifyFiltersCondition($filters, &$where)
    {
        $teamRepo = new TeamRepository();
        $templateRepo = new TemplateRepository();

        if (!empty($this->searchValue))
            $this->useAndInWhere = false;

        $searchValues = explode(":", $this->searchValue);

        $teams = $teamRepo->findAll(trim($searchValues[0]));

        $teamIds = [];
        foreach ($teams as $team) {
            $teamIds[] = $team->getId();
        }

        if (array_key_exists(1, $searchValues)) {
            $templateName = trim($searchValues[1]);
            $templates = $templateRepo->findAllByFilters(!empty($teamIds) ? ["team_id" => $teamIds] : ["team_id" => -1], $templateName);
        } else {
            $templates = $templateRepo->findAll(trim($searchValues[0]));
            $templates = array_merge($templates, $templateRepo->findAllByFilters(!empty($teamIds) ? ["team_id" => $teamIds] : ["team_id" => -1]));
        }


        $templateIds = [];
        foreach ($templates as $template) {
            $templateIds[] = $template->getId();
        }

        if (is_array($templateIds) && !empty($templateIds)) {
            $where['template_id IN (?)'] = $templateIds;
        } else {
            $where['template_id=?'] = -1;
        }
    }

    protected function getAuditlogObjectNameColumn(&$paramsToAuditlogFunctions)
    {
        $paramsToAuditlogFunctions['objectName'] = "tt";
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

        return $result;
    }

    /**
     * Overrides function to fetch the filter data order by name in ASC order.
     * @param null $value
     * @param null $direction
     * @return array
     */
    public function getOrderByParams($value = null, $direction = null)
    {
        return ['weekday' => SORT_ASC, 'start_time' => SORT_ASC];
    }


    /**
     * This function takes search value, params and return array of converted filters to db like query
     *
     * @param $searchValue
     * @param $searchParameters
     * @return array|null
     */
    protected function getSearchFilter($searchValue, $searchParameters)
    {
        $search = [];
        if (!empty($searchValue)) {
            foreach ($searchParameters as $id => $key) {
                $search[] = [BaseModel::convertCodeNamesToSnakeCaseNames($key) . ' like ?' => '%' . $searchValue . '%'];
            }
        }

        return $search;
    }
}
