<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;

class ScheduleModel extends BaseModel
{

    const EMPTY_STRING = "/";
    const USER_VALUES_DELIMITER = "|";

    /**
     * @var int $templateId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Template
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\TemplateRepository
     * @form_input_options_display_attr name
     * @translate_tooltip Name of the template to use for the schedule
     */
    private $templateId;

    /**
     * @var string $startDate
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Date
     * @form_input_type date
     * @translate_tooltip Date of the schedule
     */
    private $startDate = "";

    /**
     * @var string $startTime
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Start time
     * @form_input_type time
     * @translate_tooltip Start time of the schedule
     */
    private $startTime = "";

    /**
     * @var string $endTime
     * @db_column
     * @translate_label Endtime
     * @form_input_type time
     * @translate_tooltip Endtime of new inserted schedule
     */
    private $endTime = "";

    /**
     * @var int $teamId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Team
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\TeamRepository
     * @form_input_options_display_attr name
     * @translate_tooltip Team to which the schedule belongs to
     */
    private $teamId;

    /**
     * @var int $userId
     * @db_column
     * @table_column
     * @translate_label User
     * @form_input_type hidden
     * @translate_tooltip User to use for this schedule
     */
    private $userId;

    /**
     * @var string $userName
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @search_column
     * @translate_label Username
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\IcingaUserRepository
     * @form_input_options_display_attr objectName
     * @translate_tooltip Name of the user
     */
    private $userName;

    /**
     * @var string $userPhoneNumber
     * @db_column
     * @table_column
     * @translate_label User phonenumber
     * @form_input_type hidden
     * @translate_tooltip Phone number of the user
     */
    private $userPhoneNumber;

    /**
     * @var int $calendarWeek
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Calendar Week
     * @form_input_type hidden
     * @translate_tooltip Number of the calendar Week
     */
    private $calendarWeek;

    /**
     * @var int $calendarYear
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Calendar Year
     * @form_input_type hidden
     * @translate_tooltip Number of the calendar Year
     */
    private $calendarYear;

    /**
     * Contract constructor.
     * @param int $id
     * @param int $templateId
     * @param object $startDate
     * @param object $startTime
     * @param object $endDate
     * @param object $endTime
     * @param int $teamId
     * @param int $userId
     * @param string $userName
     * @param string $userPhoneNumber
     * @param int $calendarWeek
     * @param int $calendarYear
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        int $templateId = null,
        $startDate,
        $startTime,
        // $endTime = null,
        $endTime,
        int $teamId = null,
        int $userId = null,
        string $userName = null,
        string $userPhoneNumber = null,
        int $calendarWeek = null,
        int $calendarYear = null
    ) {
        $this->setId($id);
        $this->setTemplateId($templateId);
        $this->setStartDate($startDate);
        $this->setStartTime($startTime);
        $this->setEndTime($endTime);
        $this->setTeamId($teamId);
        $this->setUserId($userId);
        $this->setUserName($userName);
        $this->setUserPhoneNumber($userPhoneNumber);
        $this->setCalendarWeek($calendarWeek);
        $this->setCalendarYear($calendarYear);
    }

    /**
     * @return int
     */
    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    /**
     * @param int $templateId
     */
    public function setTemplateId(int $templateId): void
    {
        $this->templateId = $templateId;
    }

    /**
     * @return TemplateModel
     */
    public function getTemplate(): TemplateModel
    {
        $templateRepository = new TemplateRepository();
        return $templateRepository->findById($this->templateId);
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    /**
     * @param string $startTime
     */
    public function setStartTime($startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->endTime ? $this->endTime : "";
    }

    /**
     * @param string $endTime
     */
    public function setEndTime($endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->teamId;
    }

    /**
     * @param int $teamId
     */
    public function setTeamId(int $teamId): void
    {
        $this->teamId = $teamId;
    }

    /**
     * @return TeamModel
     */
    public function getTeam(): TeamModel
    {
        $teamRepository = new TeamRepository();
        return $teamRepository->findById($this->teamId);
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId = null): void
    {
        $this->userId = $userId;
    }

    public function getUser()
    {
        if (!$this->userId)
            return static::EMPTY_STRING;

        $user = (new IcingaUserRepository)->findById($this->userId);

        if (!$user)
            return static::EMPTY_STRING;

        return $user->getObjectName();
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        if (!$this->userName)
            return static::EMPTY_STRING;

        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName = null): void
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserPhoneNumber(): string
    {
        if (!$this->userPhoneNumber)
            return static::EMPTY_STRING;

        return $this->userPhoneNumber;
    }

    /**
     * @param string $userPhoneNumber
     */
    public function setUserPhoneNumber(string $userPhoneNumber = null): void
    {
        $this->userPhoneNumber = $userPhoneNumber;
    }

    /**
     * @return int
     */
    public function getCalendarWeek(): int
    {
        return $this->calendarWeek;
    }

    /**
     * @param int $calendarWeek
     */
    public function setCalendarWeek(int $calendarWeek = null): void
    {
        $this->calendarWeek = $calendarWeek ? $calendarWeek : date("W", strtotime($this->startDate));
    }

    /**
     * @return int
     */
    public function getCalendarYear(): int
    {
        return $this->calendarYear;
    }

    /**
     * @param int $calendarYear
     */
    public function setCalendarYear(int $calendarYear = null): void
    {
        $this->calendarYear = $calendarYear ? $calendarYear : date("o", strtotime($this->startDate));
    }
}
