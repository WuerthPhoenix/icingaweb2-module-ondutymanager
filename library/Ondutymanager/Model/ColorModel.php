<?php


namespace Icinga\Module\Ondutymanager\Model;

use Icinga\Module\Neteye\Model\BaseModel;

class ColorModel extends BaseModel
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
     * @var string $code
     * @db_column
     * @table_column
     * @cli_create_mandatory
     * @translate_label Code
     * @form_input_type text
     * @translate_tooltip Value of the color
     */
    private $code;

    /**
     * Contract constructor.
     * @param int $id
     * @param string $name
     * @param string $code
     * @throws \Exception
     */
    public function __construct(
        int $id = null,
        string $name = null,
        string $code = null
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setCode($code);
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

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code = null): void
    {
        $this->code = $code;
    }
}
