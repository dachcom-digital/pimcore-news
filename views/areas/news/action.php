<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

class News extends Document\Tag\Area\AbstractArea {

    public function action() {

        $news = new \News\Model\Entry();

        $this->getView()->newsDetail = $this->getView()->href('detail')->getData();

        $this->getView()->news = $news->getLatest($this->getView()->checkbox('latest')->getData(), $this->getView()->href("category")->getElement(), $this->getView()->numeric('limit')->getData());
    }

}