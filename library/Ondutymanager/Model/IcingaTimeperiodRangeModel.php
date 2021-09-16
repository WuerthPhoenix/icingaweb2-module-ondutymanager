<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;

class IcingaTimeperiodRangeModel extends BaseModel
{
    /**
     * @var int $timeperiodId
     * @db_column
     */
    private $timeperiodId;

    /**
     * @var string $rangeKey
     * @db_column
     * @table_column
     */
    private $rangeKey;

    /**
     * Contract constructor.
     * @param int $timeperiodId
     * @param string $rangeKey
     * @throws \Exception
     */
    public function __construct(
        int $timeperiodId = null,
        string $rangeKey = null
    ) {
        $this->setTimeperiodId($timeperiodId);
        $this->setRangeKey($rangeKey);
    }

    /**
     * @return int
     */
    public function getTimeperiodId(): int
    {
        return $this->timeperiodId;
    }

    /**
     * @param int $timeperiodId
     */
    public function setTimeperiodId(int $timeperiodId = null): void
    {
        $this->timeperiodId = $timeperiodId;
    }

    /**
     * @return string
     */
    public function getRangeKey(): string
    {
        return $this->rangeKey;
    }

    /**
     * @param string $rangeKey
     */
    public function setRangeKey(string $rangeKey = null): void
    {
        $this->rangeKey = $rangeKey;
    }
}
