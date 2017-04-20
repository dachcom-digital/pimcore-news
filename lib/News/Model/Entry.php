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
            'entryType'            => 'all',
            'category'             => NULL,
            'includeSubCategories' => FALSE,
            'where'                => [],
            'request'              => []

        ], $params);

        $newsListing = Object\NewsEntry::getList();
        $newsListing->setOrderKey($settings['sort']['field']);
        $newsListing->setOrder($settings['sort']['dir']);
        $newsListing->addConditionParam('name <> ""');
        $newsListing->setGroupBy('o_id');

        if ($settings['entryType'] !== 'all') {
            $newsListing->addConditionParam('entryType = ?', $settings['entryType']);
        }

        $categories = NULL;
        if ($settings['category'] && $settings['category'] instanceof Category) {
            $categories = static::getCategoriesRecursive($settings['category'], $settings['includeSubCategories']);
        }

        //add optional category selector
        static::addCategorySelectorToQuery($newsListing, $categories, $settings);

        //add additional where clauses.
        if (count($settings['where'])) {

            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }
        }

        //allow listing modification.
        static::modifyListing($newsListing, $settings);

        $paginator = \Zend_Paginator::factory($newsListing);
        $paginator->setCurrentPageNumber($settings['page']);
        $paginator->setItemCountPerPage($settings['itemsPerPage']);

        return $paginator;
    }

    /**
     * add query join if categories available.
     *
     * @param Object\NewsEntry\Listing $newsListing
     * @param null                     $categories
     * @param array                    $settings
     */
    public static function addCategorySelectorToQuery($newsListing, $categories = NULL, $settings = [])
    {
        $newsListing->onCreateQuery(function (\Zend_Db_Select $query) use ($newsListing, $categories, $settings) {
            if (!empty($categories)) {
                $query->join(
                    ['relations' => 'object_relations_' . $newsListing->getClassId()],
                    "relations.src_id = o_id",
                    ''
                );
            }

            //allow query modification.
            static::modifyQuery($query, $newsListing, $settings);
        });

        if (!empty($categories)) {
            $newsListing->addConditionParam('relations.fieldname = "categories" AND relations.dest_id IN (' . rtrim(str_repeat('?,', count($categories)), ',') .')', $categories);
        }
    }

    /**
     * @param \Zend_Db_Select                         $query
     * @param \Pimcore\Model\Object\NewsEntry\Listing $listing
     * @param array                                   $settings
     */
    protected static function modifyQuery($query, $listing, $settings = [])
    {
    }

    /**
     * @param \Pimcore\Model\Object\NewsEntry\Listing $listing
     * @param array                                   $settings
     */
    protected static function modifyListing($listing, $settings = [])
    {
    }

    /**
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param bool                               $includeSubCategories
     *
     * @return array|null
     */
    public static function getCategoriesRecursive($category, $includeSubCategories = FALSE)
    {
        if (!$category) {
            return NULL;
        }

        $categories = [ $category->getId() ];

        if ($includeSubCategories === TRUE) {

            $entries = NewsCategory::getList();
            $entries->setCondition('o_path LIKE "' . $category->getFullPath() . '%"');

            foreach ($entries->load() as $entry) {
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
                    'url'    => \Pimcore\Tool::getHostUrl() . $image->getThumbnail('galleryImage')->getPath(),
                    'width'  => $image->getThumbnail('galleryImage')->getWidth(),
                    'height' => $image->getThumbnail('galleryImage')->getHeight(),
                ];
            }
        }

        return $data;
    }

}
