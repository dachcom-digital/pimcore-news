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
     * Get single image for entry
     * @return bool|\Pimcore\Model\Asset
     */
    public function getImage();

    public function getName($language = NULL);

    public function getLead($language = NULL);

    public function getDescription($language = NULL);

    public function getJsonLDData();

    public function getEntryType();

    public function getRedirectLink($language = NULL);

    public function getDetailUrl($language = NULL);

    public function getCategories();

    public function getMetaTitle();

    public function getMetaDescription();

}
