<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
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

/**
 * ScheduleInsertForm Form that is displayed when the user clicks on the
 * add in the plantable to add a new schedule on a specific date
 */
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
    }

    protected function assemble()
    {
        $this->prepareAssemble();

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
                    $modelProperty
                )
            );
        }
    }

}
