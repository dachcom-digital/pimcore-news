<?php

namespace News\Model;

use Pimcore\Model\Object;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Object\Concrete;

class Category extends Concrete {

    /**
     * Get all Categories
     * @return array
     */
    public static function getAll()
    {
        $list = new Object\NewsCategory\Listing();
        return $list->getObjects();
    }

    /**
     * Get localizedfields -
     * @return array
     */
    public function getLocalizedfields()
    {
        $preValue = $this->preGetValue('localizedfields');
        if ($preValue !== null && !\Pimcore::inAdmin()) {
            return $preValue;
        }
        $data = $this->getClass()->getFieldDefinition('localizedfields')->preGetData($this);

        return $data;
    }

    /**
     * Get first level of categories
     * @return array
     */
    public static function getFirstLevel()
    {
        $list = new Object\NewsCategory\Listing();
        $list->setCondition('parentCategory__id is null');

        return $list->getObjects();
    }

    /**
     * Returns all Child Categories from $category
     *
     * @param Category $category
     *
     * @return array
     */
    public static function getAllChildCategories(Category $category)
    {
        $allChildren = array($category->getId());

        $loopChilds = function (Category $child) use (&$loopChilds, &$allChildren) {
            $childs = $child->getChildCategories();

            foreach ($childs as $child) {
                $allChildren[] = $child->getId();

                $loopChilds($child);
            }
        };

        $loopChilds($category);

        return $allChildren;
    }

    /**
     * Get News from the Category
     *
     * @param bool $includeChildCategories
     *
     * @return array
     */
    public function getEntries($includeChildCategories = false)
    {
        $list = new Object\NewsCategory\Listing();

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

        return $list->getObjects();
    }

    /**
     * Get Products from the Category with Paging
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
    ), $includeChildCategories = false)
    {
        $list = new Object\NewsCategory\Listing();

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

    /**
     * Checks if category is child of hierachy
     *
     * @param Category $category
     * @param int      $level to check hierachy (0 = topMost)
     *
     * @return bool
     */
    public function inCategory(Category $category, $level = 0)
    {
        $mostTop = $this->getHierarchy();
        $mostTop = $mostTop[$level];

        $childs = self::getAllChildCategories($mostTop);

        return in_array($category->getId(), $childs);
    }

    /**
     * Get Level of Category
     * @return int
     */
    public function getLevel()
    {
        return count($this->getHierarchy());
    }

    /**
     * Returns all Children from this Category
     * @return array
     */
    public function getCatChilds()
    {
        return self::getAllChildCategories($this);
    }

    /**
     * Get Category hierarchy
     * @return array
     */
    public function getHierarchy()
    {
        $hierarchy = array();

        $category = $this;

        do {
            $hierarchy[] = $category;

            $category = $category->getParentCategory();
        } while ($category instanceof Category);

        return array_reverse($hierarchy);
    }

    /**
     * Get all child Categories
     * @return array
     */
    public function getChildCategories()
    {
        $list = new Object\NewsCategory\Listing();
        $list->setCondition("parentCategory__id = ?", array($this->getId()));

        return $list->getObjects();
    }

    /**
     * returns parent category
     * this method has to be overwritten in Pimcore Object
     * @throws \News\Exception
     * @return Category
     */
    public function getParentCategory()
    {
        throw new \News\Exception("getParentCategory is not supported for " . get_class($this));
    }
}
