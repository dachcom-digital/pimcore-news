<?php

use News\Plugin;
use News\Model\Configuration;
use Pimcore\Model\Object;
use Website\Controller\Action;


class News_NewsController extends Action {

    public function init() {
        parent::init();

        $this->enableLayout();
        $this->setLayout(Plugin::getLayout());

    }

    public function listAction() {

        $news = new \News\Model\Entry();
        $settings = Configuration::get('news_list_settings');

        $itemsPerPage = (int)$settings['maxItems']['paginate']['itemsPerPage'];

        if ($this->document->getProperty("news_list_items_per_page")) {
            $itemsPerPage = (int)$this->document->getProperty("news_list_items_per_page");
        }

        $detailDocument = null;
        if ($this->document->getProperty("news_list_detail")) {
            $detailDocument = $this->document->getProperty("news_list_detail");
        }

        $category = null;
        if ($this->document->getProperty("news_category") && $this->document->getProperty("news_category") instanceof Object\NewsCategory) {
            $category = $this->document->getProperty("news_category");
        }

        $this->view->assign('itemsPerPage', $itemsPerPage);
        $this->view->assign('category', $category);
        $this->view->assign('detailDocument', $detailDocument);
        $this->view->assign('document', $this->getDocument());

        $this->view->assign('paginator', $news->getEntriesPaging($category, $this->getRequestParam("page", 0), $itemsPerPage));

    }

    public function detailAction() {

        $newsEntry = new \News\Model\Entry();

        $news = $newsEntry->getById($this->getParam('news'));

        if ( !($news instanceof Object\NewsEntry)) {

            throw new Exception("Object with the ID " . $this->getParam('news') . " doesn't exists");

        }
        else {
            $this->view->assign('document', $this->getDocument());
            $this->view->assign('news', $news->getById($this->getParam("news")));

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

}
