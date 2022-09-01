<?php

namespace Icinga\Module\Ondutymanager\Web;

use Icinga\Module\Geomap\Web\Components\Content;
use Icinga\Module\Geomap\Web\Components\Controls;
use Icinga\Module\Geomap\Web\Components\Molecules\Tabs;
use Icinga\Web\Controller as C;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Response_Abstract;

/**
 * Controller only used for the ConfigController because it has some
 * little specialities that the normal Icinga\Web\Controller does not 
 * have.
 */
class Controller extends C
{

    /** @var Controls */
    private $controls;

    /** @var Content */
    private $content;

    protected $permissionUtil;
    protected $mapsRepository;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->setViewScript('default');
    }

    /**
     * @return Controls
     */
    public function controls()
    {
        if ($this->controls === null) {
            $this->view->controls = $this->controls = new Controls();
        }

        return $this->controls;
    }

    /**
     * @return Content
     */
    public function content()
    {
        if ($this->content === null) {
            $this->view->content = $this->content = new Content();
        }

        return $this->content;
    }

    /**
     * @return Tabs
     */
    public function tabs(Tabs $tabs = null)
    {
        if ($tabs === null) {
            return $this->controls()->getTabs();
        } else {
            $this->controls()->setTabs($tabs);
            return $tabs;
        }
    }

    protected function setViewScript($script)
    {
        $this->_helper->viewRenderer->setNoController();
        $this->_helper->viewRenderer->setScriptAction($script);
    }

    /**
     * Set the title of the view. It will be show in the browser tab
     * @param $title
     */
    protected function setViewTitle($title)
    {
        $args = func_get_args();
        array_shift($args);
        $this->view->title = vsprintf($title, $args);
    }
}
