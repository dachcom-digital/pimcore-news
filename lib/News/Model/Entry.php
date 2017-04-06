<?php

namespace News\Model;

use Pimcore\Model\Object;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\NewsCategory;

class Entry extends Concrete
{
    /**
     * Get all News
     * @return array
     */
    public static function getAll()
    {
        $newsListing = Object\NewsEntry::getList();
        static::modifyListing($newsListing);
        return $newsListing->getObjects();
    }

    /**
     * Get News from the Category with Paging
     *
     * @param array $params
     *
     * @return \Zend_Paginator
     */
    public static function getEntriesPaging(array $params = [])
    {
        $settings = array_merge([
            'sort'                 => [
                'field' => 'date',
                'dir'   => 'desc'
            ],
            'page'                 => 0,
            'itemsPerPage'         => 10,
            'category'             => NULL,
            'includeSubCategories' => FALSE,
            'where'                => []

        ], $params);

        $newsListing = Object\NewsEntry::getList();
        $newsListing->setOrderKey($settings['sort']['field']);
        $newsListing->setOrder($settings['sort']['dir']);
        $newsListing->addConditionParam('name <> ""');
        $newsListing->setGroupBy('o_id');

        if (count($settings['where'])) {

            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }
        }

        if ($settings['category'] && $settings['category'] instanceof \News\Model\Category) {

            $categories = static::getCategoriesRecursive($settings['category'], $settings['includeSubCategories']);

            if (!empty($categories)) {

                $newsListing->onCreateQuery(function (\Zend_Db_Select $query) use ($newsListing, $categories) {
                    $query->join(['relations' => 'object_relations_' . $newsListing->getClassId()], "relations.src_id = o_id AND relations.fieldname = 'categories'", '');
                    static::modifyQuery($query, $newsListing);
                });

                $newsListing->addConditionParam('relations.dest_id IN (?)', implode(',', $categories));
            }
        }

        static::modifyListing($newsListing);

        $paginator = \Zend_Paginator::factory($newsListing);
        $paginator->setCurrentPageNumber($settings['page']);
        $paginator->setItemCountPerPage($settings['itemsPerPage']);

        return $paginator;
    }

    /**
     * @param \Zend_Db_Select $query
     * @param \Pimcore\Model\Object\NewsEntry\Listing $listing
     */
    protected static function modifyQuery($query, $listing)
    {
    }

    /**
     * @param \Pimcore\Model\Object\NewsEntry\Listing $listing
     */
    protected static function modifyListing($listing)
    {
    }

    /**
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param bool                               $includeSubCategories
     *
     * @return array|null
     */
    private static function getCategoriesRecursive($category, $includeSubCategories = FALSE)
    {
        if (!$category) {
            return NULL;
        }

        $categories = [];

        if (!$includeSubCategories) {
            $categories[] = $category->getId();
        } else {
            $entries = NewsCategory::getList();
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
    public function getImage()
    {
        if (count($this->getImages()) > 0) {
            return $this->getImages()[0];
        }

        return NULL;
    }

    public function getJsonLDData()
    {
        $data = [
            '@context'      => 'http://schema.org/',
            '@type'         => 'NewsArticle',
            'datePublished' => $this->getDate()->format('Y-m-d'),
            'headline'      => $this->getName(),
            'description'   => $this->getLead(),
            'articleBody'   => $this->getDescription()
        ];

        if ($this->getAuthor()) {
            $data['author'] = $this->getAuthor();
        }

        if (count($this->getImages()) > 0) {

            $image = $this->getImages()[0];

            if ($image instanceof Image) {
                $data['image'] = [
                    '@type'  => 'ImageObject',
                    'url'    => \Pimcore\Tool::getHostUrl() . $image->getThumbnail("galleryImage")->getPath(),
                    'width'  => $image->getThumbnail("galleryImage")->getWidth(),
                    'height' => $image->getThumbnail("galleryImage")->getHeight(),
                ];
            }
        }

        return $data;
    }

}
