<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

class News extends Document\Tag\Area\AbstractArea {

    public function action() {

        $news = new \News\Model\Entry();

        $view = $this->getView()->select('view')->getData();

        $this->getView()->newsDetail = $this->getView()->href('detail')->getData();
        $this->getView()->newsView = $view;

        if ($view == 'latest') {
            $this->getView()->news = $news->getLatest($this->getView()->checkbox('latest')->getData(), $this->getView()->href("category")->getElement(), $this->getView()->numeric('limit')->getData());
        } else {
            if ($view == 'list') {
                $page = $this->getRequestParam("page", 0);
                $perPage = $this->getRequestParam("perPage", $this->getView()->numeric('limit')->getData());

                $this->getView()->page = $page;
                $this->getView()->perPage = $perPage;
                $this->getView()->category = $this->getView()->href("category")->getElement();

                $this->getView()->paginator = $news->getEntriesPaging($this->getView()->checkbox('latest')->getData(), $this->getView()->href("category")->getElement(), $page, $perPage);
            }
        }
    }

    /**
     * @param string $paramName
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getRequestParam($paramName, $default = null) {
        $value = $this->getParam($paramName);
        if ((null === $value || '' === $value) && (null !== $default)) {
            $value = $default;
        }

        return $value;
    }

    public function getBrickHtmlTagOpen($brick) {
        return '<div class="news-' . $this->getView()->select('view')->getData() . '">';
    }

    public function getBrickHtmlTagClose($brick) {
        return '</div>';
    }
}