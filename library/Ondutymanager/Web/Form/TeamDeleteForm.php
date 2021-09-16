<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Neteye\Web\Form\BaseDeleteForm;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Security\SecurityException;

class TeamDeleteForm extends BaseDeleteForm
{
    public function __construct()
    {
        $this->repository = new TeamRepository();
        parent::__construct();
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
