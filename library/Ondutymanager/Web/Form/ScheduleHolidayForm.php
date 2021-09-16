<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Module\Ondutymanager\Model\ScheduleModel;
use Icinga\Module\Ondutymanager\Repository\IcingaUserRepository;
use Icinga\Module\Ondutymanager\Repository\ScheduleRepository;
use Icinga\Module\Ondutymanager\Repository\TeamRepository;
use Icinga\Module\Ondutymanager\Utils\ScheduleUtil;
use ipl\Html\FormElement\BaseFormElement;
use Icinga\Util\Translator;
use ipl\Html\Html;
use Icinga\Web\Notification;

/**
 * ScheduleInsertForm Form that is displayed when the user clicks on the
 * add in the plantable to add a new schedule on a specific date
 */
class ScheduleHolidayForm extends ScheduleForm
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

            $modelObject = $this->createModelObject();

            $scheduleRepo = new ScheduleRepository($modelObject->getTeamId());
            $schedulesToDelete = $scheduleRepo->getSchedulesOfCalendarWeek($modelObject->getCalendarWeek(), $modelObject->getCalendarYear())[$modelObject->getStartDate()];

            foreach ($schedulesToDelete as $schedule)
                $this->deleteModelObject($schedule);

            $this->insertModelObject($modelObject);

            Notification::success("Success inserting the new schedule");
            $params = [
                'startDate' => $modelObject->getStartDate(),
                'teamId' => $modelObject->getTeamId(),
                'calendarWeek' => $modelObject->getCalendarWeek(),
                'calendarYear' => $modelObject->getCalendarYear()
            ];
            $this->baseFormUtil->redirectToAction('insert', $params);
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

    protected function getModelProperties()
    {
        return ["userName"];
    }

    protected function formatConstructorValues(&$constructor)
    {
        $constructor["startTime"] = "00:00:00";

        $teamRepo = new TeamRepository();
        $team = $teamRepo->findById($constructor["teamId"]);
        $templateId = $team->getHolidayTemplateId();
        $constructor["templateId"] = $templateId;
    }

    /**
     * Below method will render the Form Element of the passed model property in a standard way
     * @param string $modelProperty
     * @param string $model
     *
     * @return BaseFormElement
     * @throws \Exception
     */
    protected function renderUserNameProperty(string $modelProperty, string $model): BaseFormElement
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
            $elementType = str_replace(array("\n", "\r"), '', trim($docValues['@form_input_type']));

            $teamId = $this->getIcingaRequest()->getParam("teamId");
            $createElementAttributes['options'] = (new IcingaUserRepository)->getUserOptionsOfTeam($teamId);

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

        return $this->createElement($elementType, "userName", $createElementAttributes);
    }
}

function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}
