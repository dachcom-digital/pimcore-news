<?php

namespace News\Helper\View;

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
     * @param       $news
     * @param array $additionalUrlParams
     *
     * @return string
     */
    public function getDetailUrl($news, $additionalUrlParams = [])
    {
        $href = NULL;
        $isRedirectLink = FALSE;

        if ($news->getRedirectLink() instanceof \Pimcore\Model\Document) {
            $href = $news->getRedirectLink()->getFullPath();
            $isRedirectLink = TRUE;
        }

        if (is_null($href)) {
            $staticRouteName = $news->getEntryType() === 'press' ? 'press_detail' : 'news_detail';

            $params = array_merge([
                'lang' => $this->view->language,
                'news' => $news->getDetailUrl($this->view->language)
            ], $additionalUrlParams);

            $href = $this->view->url($params, $staticRouteName, TRUE);
        }

        $absPath = $this->view->serverUrl() . $href;

        $cmdEv = \Pimcore::getEventManager()->trigger('news.detail.url', NULL, ['url' => $absPath, 'isRedirectLink' => $isRedirectLink]);

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

            if ($backLinkPage instanceof \Pimcore\Model\Document\Page) {
                $backLink = $backLinkPage->getFullPath();
            }
        }

        if (empty($backLink)) {

            if (
                isset($_SERVER['HTTP_REFERER'])
                && preg_match('@^[^/]+://[^/]+@', $_SERVER['HTTP_REFERER'])
                && strpos($_SERVER['HTTP_REFERER'], $this->view->serverUrl()) !== FALSE
            ) {
                $backLink = $_SERVER['HTTP_REFERER'];
            }
        }

        return $backLink;
    }

}