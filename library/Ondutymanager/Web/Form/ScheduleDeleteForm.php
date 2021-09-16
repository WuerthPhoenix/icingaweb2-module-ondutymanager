<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Util\Translator;
use ipl\Html\Html;
use Icinga\Module\Neteye\Web\Form\BaseDeleteForm;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use Icinga\Security\SecurityException;

class ScheduleDeleteForm extends BaseDeleteForm
{
    public function __construct()
    {
        $this->repository = new ScheduleRepository();
        parent::__construct();
    }

    /**
     * This function is override of DeleteBaseForm class function prepareAssemble
     * Needs to be ovveriden, because a schedule does not contain a name and therefore
     * it does not know what to print.
     */
    public function prepareAssemble()
    {
        $errorMessage = "";
        try {
            $header = ScheduleUtil::toString($this->object);
            // $header = "asdf";
        } catch (\Exception $exception) {
            $header = 'Error while parsing the schedule to a string';
            $errorMessage = $exception->getMessage();
            // console_log($exception->getMessage());
        }
        $title = Html::tag('h1', [], $header);
        $this->add($title);

        $hiddenObjectIdElement = $this->createElement('hidden', 'id', [
            'required' => true,
            'value' => $this->id
        ]);
        $this->add($hiddenObjectIdElement);

        if (empty($errorMessage)) {

            $this->addSubmitAndCancelButton(
                Translator::translate('Delete', 'neteye'),
                Translator::translate('Cancel', 'neteye')
            );
        } else {
            $errorLabel = Html::tag('span', [], 'Error: ' . $errorMessage);
            $this->add($errorLabel);
        }
    }

    /**
     * This method will be used to validate, if user is allowed to access the object or not
     * in below mode during delete action.
     * This method is written in the BASE FORM, which is now overridden here to validate if the user has permissions
     * to delete the object or not.
     * @throws \Exception
     */
    // protected function validateUserAccessPermission()
    // {
    //     if (!empty($this->object)) {
    //         if (!$this->repository->userAccessValidationForCategoryObject($this->id)) {
    //             throw new SecurityException('No permission for this category');
    //         }
    //     }
    // }
}


function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}
