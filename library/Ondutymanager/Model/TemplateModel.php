<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;
use Icinga\Module\Ondutymanager\Repository\ColorRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;

class TemplateModel extends BaseModel
{

    /**
     * @var string $name
     * @db_column
     * @table_column
     * @search_column
     * @cli_create_mandatory
     * @translate_label Name
     * @form_input_type text
     * @translate_tooltip Name of the template
     */
    private $name;

    /**
     * @var int $colorId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Color
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\ColorRepository
     * @form_input_options_display_attr name
     * @translate_tooltip Color of the template to use for the table visualisation
     */
    private $colorId;

    /**
     * @var int $teamId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Team
     * @form_input_type select
     * @form_input_options_from_db Icinga\Module\Ondutymanager\Repository\TeamRepository
     * @form_input_options_display_attr name
     * @translate_tooltip Team to which the template belongs to
     */
    private $teamId;

    /**
     * Contract constructor.
     * @param int $id
     * @param string $name
     * @param int $colorId
     * @param int $teamId
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        string $name,
        int $colorId = null,
        int $teamId = null
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setColorId($colorId);
        $this->setTeamId($teamId);
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
    public function getNameWithTeam(): string
    {
        return $this->getTeamName() . ": " . $this->name;
    }

    /**
     * @return string
     */
    public function getColorId(): string
    {
        return $this->colorId;
    }

    /**
     * @param string $colorId
     */
    public function setColorId(string $colorId = null): void
    {
        $this->colorId = $colorId;
    }

    /**
     * @return TeamModel
     */
    public function getColor(): ColorModel
    {
        $colorRepository = new ColorRepository();
        return $colorRepository->findById($this->colorId);
    }

    /**
     * @return string
     */
    public function getColorCode(): string
    {
        return $this->getColor()->getCode();
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
     * @return string
     */
    public function getTeamName(): string
    {
        $teamRepository = new TeamRepository();
        return $teamRepository->findById($this->teamId)->getName();
    }
}
