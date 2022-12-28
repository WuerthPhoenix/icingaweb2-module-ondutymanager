<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use Icinga\Module\Neteye\Web\Form\BaseForm;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Util\Translator;
use Icinga\Module\Neteye\Utils\BaseFormUtil;
use ipl\Html\Html;
use ipl\Html\HtmlElement;
use Icinga\Web\Notification;
/**
 * ScheduleCustomEditForm Form that is displayed when the user clicks on the
 * add in the plantable to add a new schedule on a specific date
 */
class ScheduleCustomEditForm extends BaseForm
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
        $this->classNameStringToRemove = 'CustomEditForm';
        parent::__construct();

        $this->id = $this->getIcingaRequest()->getParam('id');
        $this->object = $this->repository->findById($this->id);

        if (is_null($this->object)) {
            throw new \Exception('Object with id ' . $this->id . ' does not exist');
        }

        $requestParams = $this->getIcingaRequest()->getParams();
        $params = $this->getFormValues($requestParams);
        if(!empty($params)) {
            $this->populate($params);
        }
    }

    protected function assemble()
    {
        $this->prepareAssemble();

        $this->addSubmitAndDeleteButton(
            Translator::translate('Store', $this->moduleName),
            Translator::translate('Delete', $this->moduleName)
        );
    }

        /**
     * This function will insert and update record of derived class and set notification message on success of add and edit action call.
     */
    public function onSuccess()
    {
        $error = false;
        $values = $this->getValues();

        $userInfo = explode("|", $values['user_name']);
        $userId = intval($userInfo[0]);
        $userName = $userInfo[1];
        $userPhoneNumber = $userInfo[2];

        $this->object->setTemplateId($values['template_id']);
        $this->object->setStartDate($values['start_date']);
        $this->object->setStartTime($values['start_time']);

        $this->object->setUserId($userId);
        $this->object->setUserName($userName);
        $this->object->setUserPhoneNumber($userPhoneNumber);

        if ($this->actionName == 'customedit') {
            $notificationText = Translator::translate('Object updated successfully.', 'neteye');
            $this->updateModelObject($this->object);
        }

        if (!$error) {
            Notification::success($notificationText);
            $params = [
                'id' => $this->id,
                'team_id' => $this->object->getTeamId(),
                'start_date' => $values['start_date'],
                'start_time' => $values['start_time'],
                'template_id' => $values['template_id']
            ];
            $this->baseFormUtil->redirectToAction('customedit', $params);
        }
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
        $remove = [4,5,6,8,9,10]; // The following "endTime, teamId, userId, userPhoneNumber, calendarWeek, calendarYear" are to be removed
        $modelProperties = array_diff_key($modelProperties, array_flip($remove));
        // var_dump($modelProperties);

        $fields = $this->prepareModelPropertyFormElements($modelProperties, $modelName);

        $this->addElements($fields);
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
                $teamId = $this->getIcingaRequest()->getParam("team_id");
                $createElementAttributes['options'] = (new IcingaUserRepository)->getUserOptionsOfTeam($teamId);
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
