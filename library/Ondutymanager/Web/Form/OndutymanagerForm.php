<?php

namespace Icinga\Module\Ondutymanager\Web\Form;

use Icinga\Application\Icinga;
use Icinga\Module\Neteye\Web\Components\Atoms\Icon;
use Icinga\Module\Neteye\Web\Form\Element\CsrfCounterMeasure;
use Icinga\Module\Neteye\Web\Form\Element\DeleteElement;
use Icinga\Module\Neteye\Web\Form\Element\FormIdentificationInput;
use Icinga\Util\Translator;
use Icinga\Web\Response;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Form;
use ipl\Html\FormElement\BaseFormElement;
use GuzzleHttp\Psr7\ServerRequest;
use ipl\Html\FormElement\SubmitElement;
use ipl\Html\Html;
use ipl\Html\HtmlElement;

class OndutymanagerForm extends Form
{
    protected $tokenDisabled = false;
    protected $tokenElementName = 'CSRFToken';
    protected $elementName;
    protected $icingaRequest;
    protected $deleteButtonName;
    protected $deleteButton;

    /** @var Response */
    protected $icingaResponse;

    /**
     * OndutymanagerForm constructor.
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function __construct()
    {
        $this->prepareToHandleRequest();

        $this->addCsrfCounterMeasure();
        $this->addFormIdentification();
    }


    public function prepareToHandleRequest()
    {
        $serverRequest = $this->getPsr7Request();
        $this->handleRequest($serverRequest);
    }

    public function onError()
    {
        foreach ($this->getElements() as $element) {
            foreach ($element->getMessages() as $message) {
                $element->addWrapper(Html::tag('p', ['class' => 'error error-message'], $message));
            }
        }
    }

    /**
     * @param string $type
     * @param string $name
     * @param null   $attributes
     *
     * @return BaseFormElement
     */
    public function createElement($type, $name, $attributes = null)
    {
        $formElement = parent::createElement($type, $name, $attributes);

        $wrapper = $this->createWrapper($formElement);
        $formElement->addWrapper($wrapper);

        return $formElement;
    }

    /**
     * @param string $type
     * @param string $name
     * @param null   $attributes
     *
     * @return BaseFormElement
     */
    public function createElementInColumns($type, $name, $attributes = null)
    {
        $formElement = parent::createElement($type, $name, $attributes);

        $wrapper = $this->createWrapper($formElement, true);
        $formElement->addWrapper($wrapper);

        return $formElement;
    }

    /**
     * @param string $label
     */
    public function addSubmitButton($label, $disabled = false)
    {
        $wrapper = Html::tag('div', ['class' => 'control-group form-controls col-3-3']);

        $wrapper = $this->createSubmitButton($label, $wrapper, $disabled);

        $this->add($wrapper);
    }

    /**
     * @param string $label
     */
    public function addCancelButton($label)
    {
        $wrapper = Html::tag('div', ['class' => 'control-group form-controls']);

        $wrapper = $this->createCancelButton($label, $wrapper);

        $this->add($wrapper);
    }

    /**
     * @param string $submitLabel
     * @param string $deleteLabel
     */
    public function addSubmitAndDeleteButton($submitLabel, $deleteLabel)
    {
        $wrapper = Html::tag('div', ['class' => 'control-group form-controls']);

        $wrapper = $this->createSubmitButton($submitLabel, $wrapper);
        $wrapper = $this->createDeleteButton($deleteLabel, $wrapper);

        $this->add($wrapper);
    }

    /**
     * @param string $submitLabel
     * @param string $cancelLabel
     */
    public function addSubmitAndCancelButton($submitLabel, $cancelLabel)
    {
        $wrapper = Html::tag('div', ['class' => 'control-group form-controls col-3-3']);

        $wrapper = $this->createSubmitButton($submitLabel, $wrapper);
        $wrapper = $this->createCancelButton($cancelLabel, $wrapper);

        $this->add($wrapper);
    }

    /**
     * @param string $label
     * @param BaseHtmlElement $wrapper
     *
     * @return BaseHtmlElement
     *
     */
    protected function createSubmitButton($label, $wrapper, $disabled = false)
    {
        if ($label === null) {
            $label = Translator::translate('Store', 'ondutymanager');
        }

        $btn = new SubmitElement($label, ["disabled" => $disabled]);
        $btn->setLabel($label);

        $this->setSubmitButton($btn);

        $wrapper->add($btn);

        return $wrapper;
    }

    /**
     * @param string $label
     * @param BaseHtmlElement $wrapper
     *
     * @return BaseHtmlElement mixed
     */
    protected function createDeleteButton($label, $wrapper)
    {
        if ($label === null) {
            $label = Translator::translate('Delete', 'ondutymanager');
        }

        $btn = new DeleteElement($label);
        $btn->setLabel($label);

        $this->deleteButtonName = $btn->getName();
        $this->setDeleteButton($btn);

        $wrapper->add($btn);

        return $wrapper;
    }

    /**
     * @param string $label
     * @param BaseHtmlElement $wrapper
     *
     * @return BaseHtmlElement mixed
     */
    protected function createCancelButton($label, $wrapper)
    {
        if ($label === null) {
            $label = Translator::translate('Cancel', 'ondutymanager');
        }

        $btn = $this->createElement(
            'submit',
            'cancel',
            [
                'id' => 'deleteMapCancel',
                'class' => 'button-no-label cancel_delete'
            ]
        );
        $btn->setLabel($label);
        $btn->setWrapper(Html::tag('span'));

        $wrapper->add($btn);

        return $wrapper;
    }

    /**
     * @return bool
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function hasBeenSubmitted()
    {
        if ($this->hasSubmitButton()) {
            $name = $this->getSubmitButton()->getName();
            return $this->getSentValue($name) === $this->getSubmitButton()->getButtonLabel();
        } else {
            return $this->hasBeenSent();
        }
    }

    /**
     * @return bool
     */
    public function hasDeleteButton()
    {
        return $this->deleteButtonName !== null;
    }

    /**
     * @return bool
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function shouldBeDeleted()
    {
        if (! $this->hasDeleteButton()) {
            return false;
        }

        $name = $this->deleteButtonName;
        return $this->getSentValue($name) === $this->getDeleteButton($name)->getButtonLabel();
    }

    /**
     * @param      $name
     * @param null $default
     *
     * @return mixed|null
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function getSentValue($name, $default = null)
    {
        $request = $this->getIcingaRequest();
        if ($request->isPost() && $this->hasBeenSent()) {
            return $request->getPost($name);
        } else {
            return $default;
        }
    }

    /**
     * @param null $default
     *
     * @return mixed|null
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function getSentValues($default = null)
    {
        $request = $this->getIcingaRequest();
        if ($request->isPost() && $this->hasBeenSent()) {
            return $request->getPost();
        } else {
            return $default;
        }
    }


    /**
     * @param $formElement
     *
     * @return HtmlElement
     */
    protected function createWrapper($formElement, $inColumns = false)
    {
        $wrapper = Html::tag('div', ['class' => 'control-group' . ($inColumns ? " col-1-3" : "")]);
        $labelText = $formElement->getLabel();
        if (!is_null($labelText)) {
            $required = (($formElement->isRequired()) ? 'required' : '');
            $label = $this->createLabel($formElement, $labelText, $required);
            $wrapper->add($label);
        }

        return $wrapper;
    }

    /**
     * @param $formElement
     * @param $label
     * @param $required
     *
     * @return HtmlElement
     */
    protected function createLabel($formElement, $label, $required)
    {
        $description = $formElement->getDescription();

        $labelTag = Html::tag('label', ['class' => 'control-label ' . $required], $label);

        if ($required !== '') {
            $requiredSymbol = Html::tag('span', ['aria-hidden' => true], '*');
            $labelTag->add($requiredSymbol);
        }

        $labelTag->addWrapper(Html::tag('span'));

        $labelTag->addWrapper(Html::tag('div', ['class' => 'control-label-group']));

        if (!is_null($description)) {
            $labelTag->add($this->createDescription($description));
        }

        return $labelTag;
    }

    /**
     * Add CSRF counter measure field to this form
     *
     * @return  $this
     * @throws \Icinga\Exception\ProgrammingError
     */
    private function addCsrfCounterMeasure()
    {
        if (! $this->tokenDisabled) {
            if (! $this->getIcingaRequest()->isXmlHttpRequest()
                && ($this->getIcingaRequest()->isApiRequest())
            ) {
                return $this;
            }
            if ($this->hasElement($this->tokenElementName) === false) {
                $this->addElement(new CsrfCounterMeasure($this->tokenElementName));
            }
        }
        return $this;
    }

    private function addFormIdentification()
    {
        if ($this->hasElement($this->getElementName()) === false) {
            $this->addElement((new FormIdentificationInput('formUID'))->setValue($this->getElementName()));
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getElementName()
    {
        if ($this->elementName == null) {
            $this->setElementName();
        }
        return $this->elementName;
    }

    /**
     * @param mixed $elementName
     */
    public function setElementName()
    {
        $this->elementName = str_replace('\\', '', get_class($this));
    }

    /**
     * @param $title
     *
     * @return HtmlElement
     */
    protected function createLegend($title)
    {
        return Html::tag('legend', [], $title);
    }

    /**
     * @param array $formElements
     *
     * @return HtmlElement
     */
    protected function addElements(array $formElements)
    {
        foreach ($formElements as $field) {
            $this->addElement($field);
        }
    }

    /**
     * @param $description
     *
     * @return Icon
     */
    protected function createDescription($description)
    {
        $i = new Icon('info-circled');
        $i->addAttributes([
            'class' => 'control-info',
            'aria-hidden' => true,
            'role' => 'img',
            'title' => $description
        ]);

        return $i;
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    private function getPsr7Request()
    {
        $serverRequest = ServerRequest::fromGlobals();
        return $serverRequest;
    }

    /**
     * @return bool
     */
    public function isTokenDisabled()
    {
        return $this->tokenDisabled;
    }

    /**
     * @param bool $tokenDisabled
     */
    public function setTokenDisabled($tokenDisabled)
    {
        $this->tokenDisabled = $tokenDisabled;
    }

    /**
     * Return the request associated with this form
     *
     * Returns the global request if none has been set for this form yet.
     *
     * @return \Icinga\Web\Request
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function getIcingaRequest()
    {
        if ($this->icingaRequest === null) {
            $this->icingaRequest = Icinga::app()->getRequest();
        }

        return $this->icingaRequest;
    }

    /**
     * @return Response
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function getIcingaResponse()
    {
        if ($this->icingaResponse === null) {
            $this->icingaResponse = Icinga::app()->getResponse();
        }

        return $this->icingaResponse;
    }

    /**
     * @return string
     * @throws \Icinga\Exception\ProgrammingError
     */
    public function getRequestAction()
    {
        return $this->getIcingaRequest()->getActionName();
    }

    /**
     * @return mixed
     */
    public function getDeleteButton()
    {
        return $this->deleteButton;
    }

    /**
     * @param mixed $deleteButton
     */
    public function setDeleteButton($deleteButton)
    {
        $this->deleteButton = $deleteButton;
    }
}