<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Neteye\Web\Form\BaseForm;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use Icinga\Security\SecurityException;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Util\Translator;
use ipl\Html\Html;

/**
 * ScheduleForm hides a lot of properties because they are not intended for the user to set values
 * They are calculated in the background
 */
class ScheduleForm extends BaseForm
{

    public function __construct()
    {
        $this->addElementLoader('Icinga\Module\Neteye\Web\Form\Element');

        parent::__construct();
    }

    protected function getModelProperties()
    {
        $modelName = $this->repository->getModel();
        return $modelName::getAllDbColumnProperties();
    }

    protected function createModelObject()
    {
        $modelObject = null;

        $params = $this->getIcingaRequest()->getParams();

        $constructor = array_flip(ScheduleModel::getConstructorParameterOrderedList());
        $constructor = array_fill_keys(array_keys($constructor), null);

        array_filter($params, function ($v, $k) use (&$constructor) {
            if (array_key_exists($k, $constructor))
                $constructor[$k] = $v;
        }, ARRAY_FILTER_USE_BOTH);

        $this->formatConstructorValues($constructor);

        $modelObject = $this->repository->convertToSingleModelObject($constructor);
        ScheduleUtil::formatUserData($modelObject);

        return $modelObject;
    }

        
    /**
     * formatConstructorValues override in class for custom formatting of values
     *
     * @param  mixed $constructor
     * @return void
     */
    protected function formatConstructorValues(&$constructor)
    {
    }

    /**
     * This populates the form.
     * Implement this in the extending class
     */
    protected function prepareAssemble()
    {
        // main properties set
        $mainPropertiesSet = Html::tag(
            'legend',
            [],
            Translator::translate('Main properties', $this->moduleName)
        )->addWrapper(Html::tag('fieldset'));
        $this->add($mainPropertiesSet);

        $modelName = $this->repository->getModel();
        $modelProperties = $this->getModelProperties();

        $fields = $this->prepareModelPropertyFormElements($modelProperties, $modelName);

        $this->addElements($fields);
    }

    protected function renderEndTimeProperty(string $modelProperty, string $model): BaseFormElement
    {
        return $this->createElement(
            'hidden',
            $model::getColumnName($modelProperty)
        );
    }

    protected function renderCalendarWeekProperty(string $modelProperty, string $model): BaseFormElement
    {
        return $this->createElement(
            'hidden',
            $model::getColumnName($modelProperty)
        );
    }

    protected function renderCalendarYearProperty(string $modelProperty, string $model): BaseFormElement
    {
        return $this->createElement(
            'hidden',
            $model::getColumnName($modelProperty)
        );
    }

    protected function renderUserIdProperty(string $modelProperty, string $model): BaseFormElement
    {
        return $this->createElement(
            'hidden',
            $model::getColumnName($modelProperty)
        );
    }

    protected function renderUserNameProperty(string $modelProperty, string $model): BaseFormElement
    {
        $requestParams = $this->getIcingaRequest()->getParams();

        $value = "";

        if (array_key_exists("id", $requestParams)) {
            $params = $this->getFormValues($requestParams);
            $value = $params["user_id"] . ScheduleModel::USER_VALUES_DELIMITER . $params["user_name"] . ScheduleModel::USER_VALUES_DELIMITER . $params["user_phone_number"];
        }
        return $this->renderProperty($modelProperty, $model)->setValue($value);
    }


    protected function renderUserPhoneNumberProperty(string $modelProperty, string $model): BaseFormElement
    {
        return $this->createElement(
            'hidden',
            $model::getColumnName($modelProperty)
        );
    }


    /**
     * prepareSelectElementOptionsAttribute checks if the option to displayed is a icingauser.
     * If yes, use the custom function to get the users as options, otherwise call the parent
     * function and prepare the Select like it is normal
     *
     * @return void
     */
    protected function prepareSelectElementOptionsAttribute(
        array $docValues,
        string $modelProperty,
        &$createElementAttributes
    ): void {
        if ($docValues['@form_input_options_from_db'] == "Icinga\Module\Ondutymanager\Repository\IcingaUserRepository") {
            $createElementAttributes['options'] = (new IcingaUserRepository())->getUserOptions();
        } else {
            parent::prepareSelectElementOptionsAttribute($docValues, $modelProperty, $createElementAttributes);
        }
    }

    /**
     * This will insert record using derived class repository,
     * but first it formats the object
     * @param $modelObject
     */
    protected function insertModelObject($modelObject)
    {
        ScheduleUtil::formatUserData($modelObject);

        $this->id = $this->repository->add($modelObject);
    }

    /**
     * This will update record using derived class repository,
     * but first it formats the object
     * @param $modelObject
     */
    protected function updateModelObject($modelObject)
    {
        ScheduleUtil::formatUserData($modelObject);

        $this->repository->update($modelObject);
    }

    /**
     * Override: Below method will be used to fetch the full class namespace of the ScheduleModel using
     * the child FormClass who extends the BaseForm class.
     *
     * @param string $newClassType
     * @return string
     * @throws ReflectionException
     */
    protected function getClassName(string $newClassType = 'Model'): string
    {
        $className =  "Schedule" . $newClassType;
        $namespace = $this->getNamespaceName();
        return $namespace . $newClassType . '\\' . $className;
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
