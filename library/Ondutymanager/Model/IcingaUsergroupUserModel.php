<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;

/**
 * IcingaUsergroupUserModel this Model does not respect the rules of a BaseModel,
 * because it is the model of a pivot table in the director database, which
 * does not have a id, but a combined id of the usergroup_id and user_id
 */
class IcingaUsergroupUserModel extends BaseModel
{
    /**
     * @var int $usergroupId
     * @db_column
     * @table_column
     * @search_column
     * @cli_create_mandatory
     * @translate_label Name
     * @form_input_type text
     * @translate_tooltip Name of the user
     */
    private $usergroupId;

    /**
     * @var int $userId
     * @db_column
     * @table_column
     * @search_column
     * @cli_create_mandatory
     * @translate_label Name2
     * @form_input_type text
     * @translate_tooltip Name of the user
     */
    private $userId;

    /**
     * Contract constructor.
     * @param int $usergroupId
     * @param int $userId
     * @throws \Exception
     */
    public function __construct(
        int $usergroupId = null,
        int $userId = null
    ) {
        $this->setUsergroupId($usergroupId);
        $this->setUserId($userId);
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
}
