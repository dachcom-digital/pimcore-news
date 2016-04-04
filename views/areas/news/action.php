<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

class News extends Document\Tag\Area\AbstractArea {

    public function action() {

        $news = new \Pimcore\Model\Object\NewsEntry();

        $this->view->news = $news->getLatestNews($this->view->checkbox('latest')->getData(), $this->view->href("category")->getElement(), $this->view->numeric('limit')->getData());

    }

}