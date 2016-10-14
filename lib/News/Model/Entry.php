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
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param int                                $page
     * @param int                                $itemsPerPage
     * @param array                              $sort
     * @param bool                               $showOnlyTopNews
     *
     * @return \Zend_Paginator
     */
    public function getEntriesPaging($category = null, $page = 0, $itemsPerPage = 10, $sort = ['field' => 'date', 'dir'   => 'desc'], $showOnlyTopNews = false) {

        $list = new Object\NewsEntry\Listing();

        $where = "name IS NOT NULL ";

        if ($category) {
            $where .= " AND categories LIKE '%," . $category->getId() . ",%' ";
        }

        if ($showOnlyTopNews === true) {
            $where .= " AND latest = 1";
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
