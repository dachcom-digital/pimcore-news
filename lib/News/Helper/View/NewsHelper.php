<?php

namespace News\Helper\View;

class NewsHelper extends \Zend_View_Helper_Abstract {

    public function newsHelper()
    {
        return $this;
    }

    /**
     * @param       $news
     * @param array $additionalUrlParams
     *
     * @return string
     */
    public function getDetailUrl( $news, $additionalUrlParams = array() )
    {
        $href = NULL;
        $isRedirectLink = FALSE;

        if($news->getRedirectLink() instanceof \Pimcore\Model\Document)
        {
            $href = $news->getRedirectLink()->getFullPath();
            $isRedirectLink = TRUE;
        }

        if( is_null($href) )
        {
            $params = array_merge( [
                'lang'      => $this->view->language,
                'name'      => \Pimcore\File::getValidFilename($news->getName()),
                'news'      => $news->getId()
            ], $additionalUrlParams );

            $href = $this->view->url($params, 'news_detail', TRUE);

        }

        $absPath = $this->view->serverUrl() . $href;

        $cmdEv = \Pimcore::getEventManager()->trigger('news.detail.url', NULL, array('url' => $absPath, 'isRedirectLink' => $isRedirectLink));

        if ($cmdEv->stopped())
        {
            $absPath = $cmdEv->last();
        }

        return $absPath;

    }
}