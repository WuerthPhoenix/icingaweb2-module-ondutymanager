<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;

class IcingaUserModel extends BaseModel
{
    /**
     * @var string $objectName
     * @db_column
     * @table_column
     * @search_column
     * @cli_create_mandatory
     * @translate_label Name
     * @form_input_type text
     * @translate_tooltip Name of the user
     */
    private $objectName;

    /**
     * Contract constructor.
     * @param int $id
     * @param string $objectName
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        string $uuid = null,
        string $objectName = null
    ) {
        $this->setId($id);
        $this->setObjectName($objectName);
    }

    /**
     * @return string
     */
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * @param string $objectName
     */
    public function setObjectName(string $objectName = null): void
    {
        $this->objectName = $objectName;
    }

}
