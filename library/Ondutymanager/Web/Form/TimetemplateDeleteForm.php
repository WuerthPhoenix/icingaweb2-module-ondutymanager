<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Util\Translator;
use ipl\Html\Html;
use Icinga\Module\Neteye\Web\Form\BaseDeleteForm;
use Icinga\Module\Ondutymanager\Repository\TimetemplateRepository;
use Icinga\Security\SecurityException;

class TimetemplateDeleteForm extends BaseDeleteForm
{
    public function __construct()
    {
        $this->repository = new TimetemplateRepository();
        parent::__construct();
    }

    /**
     * This function is override of DeleteBaseForm class function prepareAssemble
     * Needs to be ovveriden, because a timetemplate does not contain a name and therefore
     * it does not know what to print.
     */
    public function prepareAssemble()
    {
        try {
            $template = $this->object->getTemplate();
            $weekday = $this->object->getWeekday();
            $start_time = $this->object->getStartTime();
            $header = $template . ", " . $weekday . ": " . $start_time;
        } catch (\Exception $exception) {
            $name = '';
        }
        $title = Html::tag('h1', [], $header);
        $this->add($title);

        $hiddenObjectIdElement = $this->createElement('hidden', 'id', [
            'required' => true,
            'value' => $this->id
        ]);
        $this->add($hiddenObjectIdElement);

        $deleteLabel = Html::tag('span', [], Translator::translate('Are you sure you want to continue?', 'neteye'));
        $this->add($deleteLabel);

        $this->addSubmitAndCancelButton(
            Translator::translate('Delete', 'neteye'),
            Translator::translate('Cancel', 'neteye')
        );
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
