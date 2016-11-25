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
     * Get News from the Category with Paging
     *
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param bool                               $includeSubCategories
     * @param int                                $page
     * @param int                                $itemsPerPage
     * @param array                              $sort
     * @param bool                               $showOnlyTopNews
     *
     * @return \Zend_Paginator
     */
    public static function getEntriesPaging($category = null, $includeSubCategories = false, $page = 0, $itemsPerPage = 10, $sort = ['field' => 'date', 'dir'   => 'desc'], $showOnlyTopNews = false) {

        $list = new Object\NewsEntry\Listing();

        $where = 'name <> "" ';

        if ($showOnlyTopNews === true) {
            $where .= 'AND latest = 1 ';
        }

        if ($category) {

            $categories = self::getCategoriesRecursive($category, $includeSubCategories);

            if (!empty($categories)) {

                $list->onCreateQuery(function (\Zend_Db_Select $query) use ($list, $categories) {
                    $query->join(
                        ['relations' => 'object_relations_' . $list->getClassId()],
                        "relations.src_id = o_id AND relations.fieldname = 'categories'",
                        ''
                    );
                });

                $where .= 'AND relations.dest_id IN (' . implode(',', $categories) . ')';

            }

        }

        $list->setCondition($where);

        $list->setOrderKey($sort['field']);
        $list->setOrder($sort['dir']);
        $list->setGroupBy('o_id');

        $paginator = \Zend_Paginator::factory($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsPerPage);

        return $paginator;
    }

    /**
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param bool                               $includeSubCategories
     *
     * @return array|null
     */
    private static function getCategoriesRecursive($category, $includeSubCategories = false) {

        if (!$category) return null;

        $categories = [];

        if (!$includeSubCategories) {
            $categories[] = $category->getId();
        }
        else {
            $entries = new Object\NewsCategory\Listing();
            $entries->setCondition("o_path LIKE '" . $category->getPath() . "%'");

            foreach ($entries as $entry) {
                $categories[] = $entry->getId();
            }
        }

        return array_values($categories);
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

    public function getJsonLDData() {

        $data = [
            '@context' => 'http://schema.org/',
            '@type' => 'NewsArticle',
            'datePublished' => $this->getDate()->format('Y-m-d'),
            'headline' => $this->getName(),
            'description' => $this->getLead(),
            'articleBody' => $this->getDescription()
        ];

        if ( $this->getAuthor() ) {
            $data['author'] = $this->getAuthor();
        }

        if (count($this->getImages()) > 0) {

            $image = $this->getImages()[0];

            if ($image instanceof \Pimcore\Model\Asset\Image) {
                $data['image'] = [
                    '@type' => 'ImageObject',
                    'url' => \Pimcore\Tool::getHostUrl() . $image->getThumbnail("galleryImage")->getPath(),
                    'width' => $image->getThumbnail("galleryImage")->getWidth(),
                    'height' => $image->getThumbnail("galleryImage")->getHeight(),
                ];
            }

        }

        return $data;

    }

}
