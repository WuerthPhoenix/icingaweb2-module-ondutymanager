<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;

/**
 * IcingaUsergroupUserModel this Model does not respect the rules of a BaseModel,
 * because it is the model of a pivot table in the director database, which
 * does not have a id, but a combined id of the usergroup_id and user_id
 */
class IcingaUserVarModel extends BaseModel
{
    /**
     * @var string $userId
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Name2
     * @form_input_type text
     * @translate_tooltip Id of the user
     */
    private $userId;

    /**
     * @var string $varname
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Varname
     * @form_input_type text
     * @translate_tooltip Name of the var
     */
    private $varname;

    /**
     * @var string $varvalue
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Varvalue
     * @form_input_type text
     * @translate_tooltip Value of the var
     */
    private $varvalue;

    /**
     * Contract constructor.
     * @param int $userId
     * @param string $varname
     * @param string $varvalue
     * @throws \Exception
     */
    public function __construct(
        int $userId = null,
        string $varname = null,
        string $varvalue = null
    ) {
        $this->setUserId($userId);
        $this->setVarname($varname);
        $this->setVarValue($varvalue);
    }

    /**
     * @return int
     */
    public function getUserId(): int
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

       /**
     * @return string
     */
    public function getVarname(): string
    {
        return $this->varname;
    }

    /**
     * @param string $varname
     */
    public function setVarname(string $varname = null): void
    {
        $this->varname = $varname;
    }

       /**
     * @return string
     */
    public function getVarValue(): string
    {
        return $this->varvalue;
    }

    /**
     * @param string $varvalue
     */
    public function setVarValue(string $varvalue = null): void
    {
        $this->varvalue = $varvalue;
    }

}
