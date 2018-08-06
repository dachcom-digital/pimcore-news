<?php

namespace NewsBundle\Model;

use Pimcore\Model\DataObject;
use Zend\Paginator\Paginator;

interface EntryInterface
{
    /**
     * Get all News
     *
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
     * @param DataObject\NewsEntry\Listing $newsListing
     * @param null                         $categories
     * @param array                        $settings
     */
    public static function addCategorySelectorToQuery($newsListing, $categories = null, $settings = []);

    /**
     * @param DataObject\NewsCategory $category
     * @param bool                    $includeSubCategories
     *
     * @return array|null
     */
    public static function getCategoriesRecursive($category, $includeSubCategories = false);

    /**
     * Get single image for entry
     *
     * @return bool|\Pimcore\Model\Asset
     */
    public function getImage();

    public function getName($language = null);

    public function getLead($language = null);

    public function getDescription($language = null);

    public function getJsonLDData();

    public function getEntryType();

    public function getRedirectLink($language = null);

    public function getDetailUrl($language = null);

    public function getCategories();

    public function getMetaTitle();

    public function getMetaDescription();

}
