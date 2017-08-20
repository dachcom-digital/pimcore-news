<?php

namespace NewsBundle\Model;

use Zend\Paginator\Paginator;

interface CategoryInterface
{
    /**
     * Get all Categories
     * @return array
     */
    public static function getAll();

    /**
     * Get localized fields -
     * @return array
     */
    public function getLocalizedfields();

    /**
     * Get first level of categories

     * @return $this
     */
    public function getFirstLevel();

    /**
     * Returns all Child Categories from $category
     *
     * @param Category $category
     *
     * @return array
     */
    public static function getAllChildCategories(Category $category);

    /**
     * Get News from the Category
     *
     * @param bool $includeChildCategories
     *
     * @return array
     */
    public function getEntries($includeChildCategories = FALSE);

    /**
     * Get Products from the Category with Paging
     *
     * @param int   $page
     * @param int   $itemsPerPage
     * @param array $sort
     * @param bool  $includeChildCategories
     *
     * @return Paginator
     */
    public function getEntriesPaging(
        $page = 0,
        $itemsPerPage = 10,
        $sort = [
            'name'      => 'name',
            'direction' => 'asc'
        ],
        $includeChildCategories = FALSE
    );

    /**
     * Checks if category is child of hierachy
     *
     * @param Category $category
     * @param int      $level to check hierarchy (0 = topMost)
     *
     * @return bool
     */
    public function inCategory(Category $category, $level = 0);

    /**
     * Get Level of Category
     * @return int
     */
    public function getLevel();

    /**
     * Returns all Children from this Category
     * @return array
     */
    public function getCatChildren();

    /**
     * Get Category hierarchy
     * @return array
     */
    public function getHierarchy();

    /**
     * Get all child Categories
     * @return array
     */
    public function getChildCategories();
}
