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

        if ($this->document->getProperty('news_list_items_per_page')) {
            $itemsPerPage = (int)$this->document->getProperty('news_list_items_per_page');
        }

        $category = null;
        if ($this->document->getProperty('news_category') && $this->document->getProperty('news_category') instanceof Object\NewsCategory) {
            $category = $this->document->getProperty('news_category');
        }

        $this->view->assign('itemsPerPage', $itemsPerPage);
        $this->view->assign('category', $category);

        $this->view->assign('paginator', $news->getEntriesPaging($category, $this->getRequestParam('page', 0), $itemsPerPage));

    }

    public function detailAction() {

        $newsEntry = new \News\Model\Entry();

        //because this is a virtual document made with static route, we append some document properties with settings, if set.
        $pageProperties = Configuration::get('news_detail_settings');

        if( !empty($pageProperties) )
        {
            foreach( $pageProperties as $pagePropertyName => $pagePropertyData )
            {
                $this->document->setProperty($pagePropertyName, $pagePropertyData['type'], $pagePropertyData['data'], false, false);
            }
        }

        $news = $newsEntry->getById($this->getParam('news'));

        if ( !($news instanceof Object\NewsEntry)) {

            throw new Exception('Object with the ID ' . $this->getParam('news') . ' doesn\'t exists');

        }
        else {
            $this->view->assign('document', $this->getDocument());
            $this->view->assign('news', $news->getById($this->getParam('news')));
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
