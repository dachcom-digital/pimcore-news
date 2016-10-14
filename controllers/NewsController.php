<?php

use Pimcore\Model\Object;

use News\Plugin;
use News\Model\Configuration;
use News\Controller\Action;

class News_NewsController extends Action {

    public function init() {

        parent::init();
        $this->enableLayout();

    }

    public function detailAction()
    {
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

        if ( !($news instanceof Object\NewsEntry))
        {
            throw new Exception('Object with the ID ' . $this->getParam('news') . ' doesn\'t exists');
        }
        else
        {
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
    public function getRequestParam($paramName, $default = null)
    {
        $value = $this->getParam($paramName);
        if ((null === $value || '' === $value) && (null !== $default))
        {
            $value = $default;
        }

        return $value;
    }

}
