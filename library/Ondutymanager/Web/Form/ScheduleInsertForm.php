<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
<<<<<<< HEAD
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Util\Translator;
=======
use Icinga\Module\Ondutymanager\Repository\TemplateRepository;
use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use Icinga\Module\Neteye\Utils\BaseFormUtil;
use Icinga\Module\Neteye\Web\Form\BaseForm;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Web\Notification;
use Icinga\Util\Translator;
use ipl\Html\HtmlElement;
use ipl\Html\Html;
>>>>>>> master

/**
 * ScheduleInsertForm Form that is displayed when the user clicks on the
 * add in the plantable to add a new schedule on a specific date
 */
<<<<<<< HEAD
class ScheduleInsertForm extends ScheduleForm
{

    public function __construct()
    {
        $this->addElementLoader('Icinga\Module\Neteye\Web\Form\Element');

        parent::__construct();
=======
class ScheduleInsertForm extends BaseForm
{

    /**
     * This function will set repositoryClass if it is not set in derived class.
     *
     * BaseForm constructor.
     * @throws ProgrammingError
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->classNameStringToRemove = 'InsertForm';
        parent::__construct();

        $requestParams = $this->getIcingaRequest()->getParams();
        $params = $this->getFormValues($requestParams);
        $params['start_date'] = $params['startDate'];
        if(!empty($params)) {
            $this->populate($params);
        }
>>>>>>> master
    }

    protected function assemble()
    {
        $this->prepareAssemble();

<<<<<<< HEAD
        $this->addSubmitButton(Translator::translate('Insert', $this->moduleName));
    }

    /**
     * Redirects to the confirm page and sends all schedule properties as param
     * @return bool
     * @throws ProgrammingError
     */
    public function hasBeenSubmitted()
    {
        if ($this->hasBeenSent() && $this->shouldBeInserted()) {
            $values = $this->getValues();


            $modelObject = $this->repository->convertToSingleModelObject($values);
            ScheduleUtil::formatUserData($modelObject);

            $calendarWeek = $this->getIcingaRequest()->getParam("calendarWeek");
            $calendarYear = $this->getIcingaRequest()->getParam("calendarYear");
            $this->baseFormUtil->redirectToAction(
                'confirm',
                [
                    'templateId' => $modelObject->getTemplateId(),
                    'startDate' => $modelObject->getStartDate(),
                    'startTime' => $modelObject->getStartTime(),
                    'endTime' => $modelObject->getEndTime(),
                    'userId' => $modelObject->getUserId(),
                    'userName' => $modelObject->getUserName(),
                    'userPhoneNumber' => $modelObject->getUserPhoneNumber(),
                    'teamId' => $modelObject->getTeamId(),
                    'calendarWeek' => $calendarWeek,
                    'calendarYear' => $calendarYear,
                ]
            );
        }
    }

    /**
     * @return bool
     * @throws ProgrammingError
     */
    public function shouldBeInserted()
    {
        $name = "Insert";
        return $this->getSentValue($name) === $this->getSubmitButton($name)->getButtonLabel();
    }

    /**
     * renderStartDateProperty renders the start date form element like it is normal,
     * but sets as value the date which is passed argument
     *
     * @param  mixed $modelProperty
     * @param  mixed $model
     * @return BaseFormElement
     */
    protected function renderStartDateProperty(string $modelProperty, string $model): BaseFormElement
    {
        $startDate = $this->renderProperty($modelProperty, $model);

        $date = $this->getIcingaRequest()->getParam("startDate");
        $startDate->setValue($date);

        return $startDate;
    }

    /**
     * renderEndTimeProperty renders the endtime custom
     *
     * @param  mixed $modelProperty
     * @param  mixed $model
     * @return BaseFormElement
     */
    protected function renderEndTimeProperty(string $modelProperty, string $model): BaseFormElement
    {
        $createElementAttributes = [];
        try {
            $docValues = $model::getModelPropertyPhpDocValues($modelProperty);

            // fetch the required attribute
            $requiredAttr = false;
            if (array_key_exists('@cli_create_mandatory', $docValues)) {
                $requiredAttr = true;
            }
            $createElementAttributes['required'] = $requiredAttr;

            // fetch the element type i.e text|textarea|select
            $elementType = 'time';

            // fetch the label attribute
            if (array_key_exists('@translate_label', $docValues)) {
                $createElementAttributes['label'] = Translator::translate($docValues['@translate_label'], $this->moduleName);
            }

            // fetch the description attribute
            if (array_key_exists('@translate_tooltip', $docValues)) {
                $createElementAttributes['description'] = Translator::translate($docValues['@translate_tooltip'], $this->moduleName);
            }
        } catch (\Exception $e) {
            throw new \Exception(
                sprintf(
                    'unable to create form element for the model property: %s',
=======
        $this->addSubmitButton(Translator::translate('Store', $this->moduleName));
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
        $modelProperties = $modelName::getAllDbColumnProperties();

        // var_dump($modelProperties);
        $remove = [5,6,8,9,10]; // The following "endTime, teamId, userId, userPhoneNumber, calendarWeek, calendarYear" are to be removed
        $modelProperties = array_diff_key($modelProperties, array_flip($remove));
        // var_dump($modelProperties);

        $fields = $this->prepareModelPropertyFormElements($modelProperties, $modelName);

        $this->addElements($fields);
    }

    /**
     * This function will insert and update record of derived class and set notification message on success of add and edit action call.
     */
    public function onSuccess()
    {
        $values = $this->getValues();

        $teamId = $this->getIcingaRequest()->getParam('teamId');
        $calendarWeek = $this->getIcingaRequest()->getParam('calendarWeek');
        $calendarYear = $this->getIcingaRequest()->getParam('calendarYear');
        $userInfo = explode("|", $values['user_name']);

        //objectValues' exists only because the base modules provided by the neteye-module assign params by order and not name
        $objectValues = []; 

        $objectValues['id'] = $values['id'];
        $objectValues['template_id'] = $values['template_id'];
        $objectValues['start_date'] = $values['start_date'];
        $objectValues['start_time'] = $values['start_time'];
        $objectValues['end_time'] = $values['end_time'];
        $objectValues['team_id'] = $teamId;
        $objectValues['user_id'] = $userInfo[0];
        $objectValues['user_name'] = $userInfo[1];
        $objectValues['user_phone_number'] = $userInfo[2];
        $objectValues['calendar_week'] = $calendarWeek;
        $objectValues['calendar_year'] = $calendarYear;

        $modelObject = $this->repository->convertToSingleModelObject($objectValues);
        ScheduleUtil::formatUserData($modelObject);

        $this->baseFormUtil->redirectToAction(
            'confirm',
            [
                'templateId' => $modelObject->getTemplateId(),
                'startDate' => $modelObject->getStartDate(),
                'startTime' => $modelObject->getStartTime(),
                'endTime' => $modelObject->getEndTime(),
                'userId' => $modelObject->getUserId(),
                'userName' => $modelObject->getUserName(),
                'userPhoneNumber' => $modelObject->getUserPhoneNumber(),
                'teamId' => $teamId,
                'calendarWeek' => $calendarWeek,
                'calendarYear' => $calendarYear,
            ]
        );
    }

    /**
     * This function set dropdown values using doc name @form_input_options_static where it is define in derived class model
     * @param array $docValues
     * @param string $modelProperty
     * @param $createElementAttributes
     * @throws \Exception
     */
    protected function prepareSelectElementOptionsAttribute(
        array $docValues,
        string $modelProperty,
        &$createElementAttributes
    ) : void {
        if (array_key_exists('@form_input_options_static', $docValues)) {
            $options = explode(',', $docValues ['@form_input_options_static']);
            $optionsAttr = [];
            foreach ($options as $option) {
                $option = trim($option);
                $optionsAttr[$option] = Translator::translate($option, $this->moduleName);
            }
            $createElementAttributes ['options'] = $optionsAttr;
        } elseif (array_key_exists('@form_input_options_from_db', $docValues) &&
            array_key_exists('@form_input_options_display_attr', $docValues)
        ) {
            if ($docValues['@form_input_options_from_db'] == "Icinga\Module\Ondutymanager\Repository\IcingaUserRepository") {
                $teamId = $this->getIcingaRequest()->getParam("teamId");
                $icingaUserRepository = new IcingaUserRepository();

                $createElementAttributes['options'] = $icingaUserRepository->createUserOptions(
                    $icingaUserRepository->findAllUsersByTeamId($teamId)
                );

            }elseif($docValues['@form_input_options_from_db'] ==  "Icinga\Module\Ondutymanager\Repository\TemplateRepository"){
                $teamId = $this->getIcingaRequest()->getParam("teamId");
                $createElementAttributes['options'] = BaseFormUtil::convertModelToOptions(
                    (new TemplateRepository)->getUserTemplateByTeam($teamId),
                    $docValues['@form_input_options_display_attr']
                );
                
            }else{
                $repository = new $docValues['@form_input_options_from_db']();
                $modelObjects = $repository->findAll();
                $createElementAttributes ['options'] = BaseFormUtil::convertModelToOptions(
                    $modelObjects,
                    $docValues['@form_input_options_display_attr']
                );
            }
        } else {
            throw new \Exception(
                sprintf(
                    'form_input_options and its supported parameters are missing for the model property: %s',
>>>>>>> master
                    $modelProperty
                )
            );
        }
<<<<<<< HEAD

        return $this->createElement($elementType, $model::getColumnName($modelProperty), $createElementAttributes);
    }

    protected function renderTeamIdProperty(string $modelProperty, string $model): BaseFormElement
    {
        $teamId = $this->getIcingaRequest()->getParam("teamId");

        return $this->createElement(
            'hidden',
            $model::getColumnName($modelProperty)
        )->setValue($teamId);
    }

    /**
     * prepareSelectElementOptionsAttribute ovveride of the parent function. Its purpose is to show only the users as options,
     * which are part of the currently selected team
     *
     * @return void
     */
    protected function prepareSelectElementOptionsAttribute(
        array $docValues,
        string $modelProperty,
        &$createElementAttributes
    ): void {
        parent::prepareSelectElementOptionsAttribute($docValues, $modelProperty, $createElementAttributes);

        if ($docValues['@form_input_options_from_db'] == "Icinga\Module\Ondutymanager\Repository\IcingaUserRepository") {
            $teamId = $this->getIcingaRequest()->getParam("teamId");
            $createElementAttributes['options'] = (new IcingaUserRepository)->getUserOptionsOfTeam($teamId);
        }
    }
=======
    }

>>>>>>> master
}
