<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;

class TimetemplateModel extends BaseModel
{

    /**
     * @var string $teamWithTemplate
     * @table_column
     * @search_column
     */
    // private $teamWithTemplate;

    /**
     * @var int $templateId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Template
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\TemplateRepository
     * @form_input_options_display_attr nameWithTeam
     * @translate_tooltip Name of the template to which this time template belongs to
     */
    private $templateId;

    /**
     * @var string $weekday
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Weekday
     * @form_input_type select
     * @form_input_options_static Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday
     * @translate_tooltip Defines the weekday of the time template
     */
    private $weekday;

    /**
     * @var string $startime
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Time
     * @form_input_type time
     * @translate_tooltip Start time of the schedule
     */
    private $startTime;

    /**
     * Contract constructor.
     * @param int $id
     * @param int $templateId
     * @param int $weekday
     * @param string $startDatetime
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        int $templateId = null,
        string $weekday = null,
        string $startTime = null
    ) {
        $this->setId($id);
        $this->setTemplateId($templateId);
        $this->setWeekday($weekday);
        $this->setStartTime($startTime);
    }

    // /**
    //  * @return string
    //  */
    // public function getTeamWithTemplate(): string
    // {
    //     return "TEAM: Timetemplate";
    // }

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
     * @return string
     */
    public function getTemplate(): string
    {
        // $templateRepository = new TemplateRepository();
        // return $templateRepository->findById($this->templateId);
        $template = (new TemplateRepository())->findById($this->templateId);
        $teamName = (new TeamRepository())->findById($template->getTeamId())->getName();
        return $teamName . ": " . $template->getName();
    }

    // /**
    //  * @return string
    //  */
    // public function getTemplateName(): string
    // {

    // }

    /**
     * @return string
     */
    public function getWeekday(): string
    {
        return $this->weekday;
    }

    /**
     * @param string $weekday
     */
    public function setWeekday(string $weekday = null): void
    {
        $this->weekday = $weekday;
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
    public function setStartTime(string $startTime = null): void
    {
        $this->startTime = $startTime;
    }
}
