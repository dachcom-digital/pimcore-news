<?php

namespace News\Model;

use Pimcore\Model\Object;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Object\Concrete;

class Entry extends Concrete {

    /**
     * Get all News
     * @return array
     */
    public static function getAll() {
        $list = new Object\NewsEntry\Listing();

        return $list->getObjects();
    }

    /**
     * Get Image for Product
     * @return bool|\Pimcore\Model\Asset
     */
    public function getImage() {
        if (count($this->getImages()) > 0) {
            return $this->getImages()[0];
        }

        return null;
    }

    /**
     * Get News from the Category with Paging
     *
     * @param int                                $latest
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param int                                $limit
     * @param array                              $sort
     *
     * @return array
     */
    public function getLatestNews($latest = 0, $category = null, $limit = 0, $sort = [
        'field' => 'date',
        'dir'   => 'asc'
    ]) {

        $settings = Configuration::get('news_latest_settings');

        $list = new Object\NewsEntry\Listing();

        $where = "name IS NOT NULL";

        if ($latest) {
            $where .= " AND latest = 1";
        }

        if ($category && $category instanceof \Pimcore\Model\Object\NewsCategory) {

            $where .= " AND categories LIKE '%," . $category->getId() . ",%' ";

        }

        $list->setCondition($where);

        if ((int)$limit == 0) {
            $limit = $settings['maxItems'];
        }

        $list->setLimit($limit);
        $list->setOrderKey($sort['field']);
        $list->setOrder($sort['dir']);

        return $list->getObjects();
    }

    /**
     * Get News from the Category with Paging
     *
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param int   $page
     * @param int   $itemsPerPage
     * @param array $sort
     *
     * @return \Zend_Paginator
     * @throws \Zend_Paginator_Exception
     */
    public function getEntriesPaging($category = null, $page = 0, $itemsPerPage = 10, $sort = array(
        'field' => 'date',
        'dir'   => 'asc'
    )) {
        $list = new Object\NewsEntry\Listing();

        $where = "name IS NOT NULL ";

        if ($category) {

            $where .= " AND categories LIKE '%," . $category->getId() . ",%' ";

        }

        $list->setCondition($where);

        $list->setOrderKey($sort['field']);
        $list->setOrder($sort['dir']);

        $paginator = \Zend_Paginator::factory($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsPerPage);

        return $paginator;
    }
}
