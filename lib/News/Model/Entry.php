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

    public function getLatest($category = null, $limit = 0, $sort = [
        'field' => 'date',
        'dir'   => 'asc'
    ]) {

        $settings = Configuration::get('news_latest_settings');

        $list = new Object\NewsEntry\Listing();

        $list->setOrderKey($sort['field']);
        $list->setOrder($sort['dir']);

        if ($category && $category instanceof \Pimcore\Model\Object\NewsCategory) {

            $list->setCondition("name IS NOT NULL AND o_published = 1 AND latest = 1 AND categories LIKE '%," . $category->getId() . ",%'");

        }
        else {

            $list->setCondition('name IS NOT NULL AND o_published = 1 AND latest = 1');

        }

        if ((int)$limit == 0) {
            $limit = $settings['maxItems'];
        }

        $list->setLimit($limit);

        return $list->getObjects();
    }

    /**
     * Get News from the Category with Paging
     *
     * @param int   $page
     * @param int   $itemsPerPage
     * @param array $sort
     * @param bool  $includeChildCategories
     *
     * @return \Zend_Paginator
     * @throws \Zend_Paginator_Exception
     */
    public function getEntriesPaging($page = 0, $itemsPerPage = 10, $sort = array(
        "name"      => "name",
        "direction" => "asc"
    ), $includeChildCategories = false) {
        $list = new Object\NewsEntry\Listing();

        if (!$includeChildCategories) {
            $list->setCondition("enabled = 1 AND categories LIKE '%," . $this->getId() . ",%'");
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = array();

            foreach ($categories as $cat) {
                $categoriesWhere[] = "categories LIKE '%," . $cat . ",%'";
            }

            $list->setCondition("enabled = 1 AND (" . implode(" OR ", $categoriesWhere) . ")");
        }

        $list->setOrderKey($sort['name']);
        $list->setOrder($sort['direction']);

        $paginator = \Zend_Paginator::factory($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsPerPage);

        return $paginator;
    }
}
