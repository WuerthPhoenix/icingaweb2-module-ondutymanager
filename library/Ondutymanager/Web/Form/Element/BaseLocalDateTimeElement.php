<?php

namespace Icinga\Module\Ondutymanager\Web\Form\Element;

use ipl\Html\FormElement\LocalDateTimeElement;
use DateTime;

class BaseLocalDateTimeElement extends LocalDateTimeElement
{
    protected $type = 'datetime-local';

    public function __construct($name, $attributes = null)
    {
        parent::__construct($name, $attributes);
        if($name === "end")
            $this->setValue(DateTime::createFromFormat(LocalDateTimeElement::FORMAT, date(LocalDateTimeElement::FORMAT, strtotime('+1 hours'))));
        else
            $this->setValue(DateTime::createFromFormat(LocalDateTimeElement::FORMAT, date(LocalDateTimeElement::FORMAT)));
    }
}