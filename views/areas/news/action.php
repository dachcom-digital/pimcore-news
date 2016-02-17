<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

class News extends Document\Tag\Area\AbstractArea {

    public function action () {

        $news = new \News\Model\Entry();

        $view = $this->getView()->select('view')->getData();

        $this->getView()->newsDetail = $this->getView()->href('detail')->getData();
        $this->getView()->newsView = $view;

        if ($view == 'latest') {
            $this->getView()->news = $news->getLatest(
                $this->getView()->href("category")->getElement(),
                $this->getView()->numeric('limit')->getData()
            );
        }
        else if ($view == 'detail') {
            $this->getView()->news = $news->getEntriesPaging();
        }

    }

    public function getBrickHtmlTagOpen($brick){
        return '<div class="news-' . $this->getView()->select('view')->getData() . '">';
    }

    public function getBrickHtmlTagClose($brick){
        return '</div>';
    }
}