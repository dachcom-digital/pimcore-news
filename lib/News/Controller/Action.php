<?php
namespace News\Controller;

use Website\Controller\Action as WebsiteAction;

class Action extends WebsiteAction
{
    public function init()
    {
        //allow website path to override templates
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts');
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/layouts');

        parent::init();

    }
}