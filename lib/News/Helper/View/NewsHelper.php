<?php

namespace News\Helper\View;

use News\Tool\NewsTypes;
use Pimcore\Model\Document;
use Pimcore\Tool;

class NewsHelper extends \Zend_View_Helper_Abstract
{
    /**
     * @return $this
     */
    public function newsHelper()
    {
        return $this;
    }

    /**
     * @param \Pimcore\Model\Object\NewsEntry $news
     * @param array                           $additionalUrlParams
     *
     * @return string
     */
    public function getDetailUrl($news, $additionalUrlParams = [])
    {
        $href = NULL;
        $isRedirectLink = FALSE;

        if ($news->getRedirectLink() instanceof Document) {
            $href = $news->getRedirectLink()->getFullPath();
            $isRedirectLink = TRUE;
        }

        $eventParams = [];

        if (is_null($href)) {
            $staticRouteInfo = NewsTypes::getRouteInfo($news->getEntryType());

            $params = array_merge([
                'news' => $news->getDetailUrl($this->view->language)
            ], $additionalUrlParams);

            if (in_array('lang', $staticRouteInfo['urlParams'])) {
                $params['lang'] = $this->view->language;
            }

            $href = $this->view->url($params, $staticRouteInfo['name'], TRUE);

            $eventParams['staticRouteName'] = $staticRouteInfo['name'];
            $eventParams['routeParams'] = $params;
        }

        $absPath = Tool::getHostUrl() . $href;

        $eventParams['url'] = $absPath;
        $eventParams['isRedirectLink'] = $isRedirectLink;

        $cmdEv = \Pimcore::getEventManager()->trigger('news.detail.url', NULL, $eventParams);

        if ($cmdEv->stopped()) {
            $absPath = $cmdEv->last();
        }

        return $absPath;
    }

    /**
     * @param       $news
     *
     * @return string
     */
    public function getBackUrl($news)
    {
        $categories = $news->getCategories();
        $backLink = '';
        if (count($categories) > 0) {
            $backLinkPage = $categories[0]->getBackLinkTarget();

            if ($backLinkPage instanceof Document\Page) {
                $backLink = $backLinkPage->getFullPath();
            }
        }

        if (empty($backLink)) {

            if (
                isset($_SERVER['HTTP_REFERER'])
                && preg_match('@^[^/]+://[^/]+@', $_SERVER['HTTP_REFERER'])
                && strpos($_SERVER['HTTP_REFERER'], Tool::getHostUrl()) !== FALSE
            ) {
                $backLink = $_SERVER['HTTP_REFERER'];
            }
        }

        return $backLink;
    }

}