<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Util\Translator;

/**
 * ScheduleInsertForm Form that is displayed when the user clicks on the
 * add in the plantable to add a new schedule on a specific date
 */
class ScheduleInsertForm extends ScheduleForm
{

    public function __construct()
    {
        $this->addElementLoader('Icinga\Module\Neteye\Web\Form\Element');

        parent::__construct();
    }

    protected function assemble()
    {
        $this->prepareAssemble();

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
                    $modelProperty
                )
            );
        }

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
}
