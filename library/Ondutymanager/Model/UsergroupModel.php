<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;

class UsergroupModel extends BaseModel
{
    /**
     * @var string $name
     * @db_column
     * @table_column
     * @search_column
     * @cli_create_mandatory
     * @translate_label Name
     * @form_input_type text
     * @translate_tooltip Name of the color
     */
    private $name;

    /**
     * Contract constructor.
     * @param int $id
     * @param string $name
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        string $name = null
    ) {
        $this->setId($id);
        $this->setName($name);
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
    public function setName(string $name = null): void
    {
        $this->name = $name;
    }
}
