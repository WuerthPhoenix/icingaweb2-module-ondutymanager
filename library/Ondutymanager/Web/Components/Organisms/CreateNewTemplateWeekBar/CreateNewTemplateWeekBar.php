<?php

namespace Icinga\Module\Ondutymanager\Web\Components\Organisms\CreateNewTemplateWeekBar;

use Icinga\Application\Icinga;
use Icinga\Module\Neteye\Html\Components\Organisms\QuickLinks\ActionBarIpl;

/**
 * CreateNewTemplateWeekBar Class which displays if called a Add button.
 * Purpose is to create add button which sends you to a confirm form for
 * creating a new empty week by template
 */
class CreateNewTemplateWeekBar extends ActionBarIpl
{

    protected $tag = 'form';
    public function __construct($request, $link)
    {
        $this->setup($request, $link);
    }

    protected function setup($request, $link)
    {
        $actionLinks = array(
            array(
                'title' => 'Add',
                'name' => 'q',
                'icon' => 'plus',
                'link' => $link,
                'class' => 'color-green',
                'target' => "_self",
                'value' => (Icinga::app())->getRequest()->getUrl()->getParam('q')

            )
        );

        $this->create($actionLinks, $request);
    }
}
