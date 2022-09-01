<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Neteye\Web\Form\BaseForm;
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;
use Icinga\Security\SecurityException;
use Icinga\Util\Translator;

class TimetemplateForm extends BaseForm
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * prepareSelectElementOptionsAttribute ovveride of the parent function. its purpose is to sort the template select box by its name,
     * but with keeping the "Not selected" option as first
     *
     * @return void
     */
    protected function prepareSelectElementOptionsAttribute(
        array $docValues,
        string $modelProperty,
        &$createElementAttributes
    ): void {
        parent::prepareSelectElementOptionsAttribute($docValues, $modelProperty, $createElementAttributes);

        if (
            array_key_exists('@form_input_options_from_db', $docValues) &&
            array_key_exists('@form_input_options_display_attr', $docValues)
        ) {
            $notSelectedString = Translator::translate('Not selected', 'neteye');
            uasort($createElementAttributes['options'], function ($a, $b) use ($notSelectedString): int {
                return $a != $notSelectedString && $a > $b;
            });
        }
    }

    /**
     * This method will be used to validate, if user is allowed to access the object or not
     * in below mode during delete action.
     * This method is written in the BASE FORM, which is now overridden here to validate if the user has permissions
     * to edit the object or not.
     * @throws \Exception
     */
    // protected function validateUserAccessPermission()
    // {
    //     if (!empty($this->object)) {
    //         if (!$this->repository->userAccessValidationForFilterObject($this->id)) {
    //             throw new SecurityException('No permission for this filter');
    //         }
    //     }
    // }
}
