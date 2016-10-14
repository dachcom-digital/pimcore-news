<?php

use Pimcore\Model\Object;

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
            $this->view->assign('news', $news);
        }

        /** @var \Pimcore\View $view */

        $href = $this->view->url([
            'lang'    => $this->view->language,
            'name'    => \Pimcore\File::getValidFilename($news->getName()),
            'news'    => $news->getId()
        ], 'news_detail', TRUE);

        $this->view->headTitle()->setTitle($news->getName());
        $this->view->headMeta()->appendName('og:title', $news->getName());
        $this->view->headMeta()->appendName('og:url', $this->view->serverUrl() . $href);
        $this->view->headMeta()->appendName('og:type', 'article');

        if ($news->getLead()) {
            $this->view->headMeta()->appendName('og:description', $this->view->formatHelper()->truncate( $news->getLead(), 150) );
        }

        if ($news->getImage() instanceof \Pimcore\Model\Asset\Image) {
            $this->view->headMeta()->appendName('og:image', $this->view->serverUrl() . $news->getImage()->getThumbnail('contentImage'));
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
