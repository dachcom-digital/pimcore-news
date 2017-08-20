<?php

namespace NewsBundle\Model;

use Pimcore\Model\Object;
use Zend\Paginator\Paginator;

interface EntryInterface
{
    /**
     * Get all News
     * @return array
     */
    public static function getAll();

    /**
     * Get News from the Category with Paging
     *
     * @param array $params
     *
     * @return Paginator
     */
    public static function getEntriesPaging(array $params = []);

    /**
     * add query join if categories available.
     *
     * @param Object\NewsEntry\Listing $newsListing
     * @param null                     $categories
     * @param array                    $settings
     */
    public static function addCategorySelectorToQuery($newsListing, $categories = NULL, $settings = []);

    /**
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param bool                               $includeSubCategories
     *
     * @return array|null
     */
    public static function getCategoriesRecursive($category, $includeSubCategories = FALSE);

    /**
     * Get Image for Product
     * @return bool|\Pimcore\Model\Asset
     */
    public function getImage();

    /**
     * @return mixed
     */
    public function getJsonLDData();

    public function getEntryType();

    public function getRedirectLink($language = NULL);

    public function getDetailUrl($language = NULL);

    public function getCategories();

}
