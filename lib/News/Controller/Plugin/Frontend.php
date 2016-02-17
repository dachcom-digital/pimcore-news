<?php

namespace News\Controller\Plugin;

class Frontend extends \Zend_Controller_Plugin_Abstract {

    /**
     * @var bool
     */
    protected $initialized = false;

    public function preDispatch() {

        /** @var \Pimcore\Controller\Action\Helper\ViewRenderer $renderer */
        $renderer = \Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
        $renderer->initView();

        /** @var \Pimcore\View $view */
        $view = $renderer->view;

        $view->addScriptPath(PIMCORE_PLUGINS_PATH . '/News/views/scripts');

        $view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts');


        $this->initialized = true;

    }

}
