<?php

use Website\Controller\Action;
use News\Plugin;

class News_NewsController extends Action {

    public function init() {
        parent::init();

        $this->enableLayout();
        $this->setLayout(Plugin::getLayout());
    }

    public function detailAction() {

        $news = new \News\Model\Entry();

        $this->view->news = $news->getById($this->getParam("news"));

    }
}
