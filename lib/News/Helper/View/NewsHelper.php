<?php

namespace News\Helper\View;

use Carbon\Carbon;
use News\Model\Entry;
use News\Tool\NewsTypes;
use Pimcore\Model\Document;
use Pimcore\Tool;
use Pimcore\Model\Object;

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

    /**
     * @param \Pimcore\Model\Object\NewsEntry $news
     * @param array                           $params
     *
     * @return array
     */
    public function getRelatedNews($news, $params = []) {

        $settings = array_merge([
            'sort'                 => [
                'field' => 'date',
                'dir'   => 'desc'
            ],
            'limit'                 => 4,
            'where'                 => [],
            'entryType'             => $news->getEntryType(),
            'includeSubCategories'  => FALSE,
            'ignoreCategory'        => FALSE

        ], $params);

        $newsListing = Object\NewsEntry::getList([
            'limit' => $settings['limit']
        ]);
        if ( is_string($settings['sort']) && strtolower($settings['sort']) === 'random' ) {
            $newsListing->setOrderKey('RAND()', FALSE);
        } else {
            $newsListing->setOrderKey($settings['sort']['field']);
            $newsListing->setOrder($settings['sort']['dir']);
        }

        $newsListing->addConditionParam('name <> ""');
        $newsListing->addConditionParam('o_id != ?', $news->getId());
        $newsListing->setGroupBy('o_id');

        if ($settings['entryType'] !== 'all') {
            $newsListing->addConditionParam('entryType = ?', $settings['entryType']);
        }

        $categories = [];
        if ( count($news->getCategories()) > 0 && !$settings['ignoreCategory']) {
            foreach ( $news->getCategories() as $category ) {
                $categories += Entry::getCategoriesRecursive($category, $settings['includeSubCategories']);
            }

            Entry::addCategorySelectorToQuery($newsListing, $categories);
        }

        //add additional where clauses.
        if (count($settings['where'])) {

            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }

        }

        return $newsListing->load();
    }

}