<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Ondutymanager\Repository\IcingaTimeperiodRangeRepository;
use Icinga\Module\Ondutymanager\Repository\IcingaTimeperiodRepository;
use Icinga\Module\Ondutymanager\Repository\IcingaUsergroupRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;

class TeamModel extends BaseModel
{
    /**
     * @var string $name
     * @db_column
     * @table_column
     * @search_column
     * @cli_create_mandatory
     * @translate_label Name
     * @form_input_type text
     * @translate_tooltip Name of the team
     */
    private $name;

    /**
     * @var string $startWeekday
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Weekday start
     * @form_input_type select
     * @form_input_options_static Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday
     * @translate_tooltip Defines with which weekday the team begins its working week with
     */
    private $startWeekday;

    /**
     * @var string $startime
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Cycle start
     * @form_input_type time
     * @translate_tooltip Defines with which time the team begins its working week on the first day with
     */
    private $startCycleTime;

    /**
     * @var int $usergroupId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Contactgroup
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\IcingaUsergroupRepository
     * @form_input_options_display_attr objectName
     * @translate_tooltip Icinga usergroup of the team
     */
    private $usergroupId;

    /**
     * @var int $holidayTimeperiodId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Timeperiod
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\IcingaTimeperiodRepository
     * @form_input_options_display_attr objectName
     * @translate_tooltip Timeperiod for holidays
     */
    private $holidayTimeperiodId;

    /**
     * @var int $holidayTemplateId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Holiday-template
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\TemplateRepository
     * @form_input_options_display_attr name
     * @translate_tooltip Template which should be used when a day is set as holiday
     */
    private $holidayTemplateId;

    /**
     * Contract constructor.
     * @param int $id
     * @param string $name
     * @param string $startWeekday
     * @param string $startCycleTime
     * @param int $usergroupId
     * @param int $holidayTimeperiodId
     * @param int $holidayTemplateId
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        string $name = null,
        string $startWeekday = null,
        string $startCycleTime = null,
        int $usergroupId = null,
        int $holidayTimeperiodId = null,
        int $holidayTemplateId = null
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setStartWeekday($startWeekday);
        $this->setStartCycleTime($startCycleTime);
        $this->setUsergroupId($usergroupId);
        $this->setHolidayTimeperiodId($holidayTimeperiodId);
        $this->setHolidayTemplateId($holidayTemplateId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStartWeekday(): string
    {
        return $this->startWeekday;
    }

    /**
     * @param string $startWeekday
     */
    public function setStartWeekday(string $startWeekday): void
    {
        $this->startWeekday = $startWeekday;
    }

    /**
     * @return string
     */
    public function getStartCycleTime(): string
    {
        return $this->startCycleTime;
    }

    /**
     * @param string $startCycleTime
     */
    public function setStartCycleTime(string $startCycleTime): void
    {
        $this->startCycleTime = $startCycleTime;
    }

    /**
     * @return int
     */
    public function getUsergroupId(): int
    {
        return $this->usergroupId;
    }

    /**
     * @param int $usergroupId
     */
    public function setUsergroupId(int $usergroupId = null): void
    {
        $this->usergroupId = $usergroupId;
    }

    /**
     * @return string
     */
    public function getUsergroup(): string
    {
        if (!$this->usergroupId)
            return ScheduleModel::EMPTY_STRING;

        $usergroup = (new IcingaUsergroupRepository)->findById($this->usergroupId);

        if (!$usergroup)
            return ScheduleModel::EMPTY_STRING;

        return $usergroup->getObjectName();
    }

    /**
     * @param int $holidayTemplateId
     */
    public function setHolidayTemplateId(int $holidayTemplateId = null): void
    {
        $this->holidayTemplateId = $holidayTemplateId;
    }

    /**
     * @return int
     */
    public function getHolidayTemplateId(): int
    {
        return $this->holidayTemplateId;
    }

    /**
     * @return TemplateModel
     */
    public function getHolidayTemplate(): TemplateModel
    {
        return (new TemplateRepository())->findById($this->holidayTemplateId);
    }

    /**
     * @param int $holidayTimeperiodId
     */
    public function setHolidayTimeperiodId(int $holidayTimeperiodId = null): void
    {
        $this->holidayTimeperiodId = $holidayTimeperiodId;
    }

    /**
     * @return int
     */
    public function getHolidayTimeperiodId(): int
    {
        return $this->holidayTimeperiodId;
    }

    /**
     * @return IcingaTimeperiodModel
     */
    public function getHolidayTimeperiod(): IcingaTimeperiodModel
    {
        return (new IcingaTimeperiodRepository())->findById($this->holidayTimeperiodId);
    }

    /**
     * @return array
     */
    public function getTimeperiodRanges(): array
    {
        return (new IcingaTimeperiodRangeRepository())->findAllRangesByTimeperiodId($this->holidayTimeperiodId);
    }
}
