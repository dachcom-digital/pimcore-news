<?php

namespace News\Model;

use Pimcore\Model\Object;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Object\Concrete;

class Category extends Concrete
{
    /**
     * Get all Categories
     * @return array
     */
    public static function getAll()
    {
        $list = Object\NewsCategory::getList();
        return $list->getObjects();
    }

    /**
     * Get localized fields -
     * @return array
     */
    public function getLocalizedfields()
    {
        $preValue = $this->preGetValue('localizedfields');
        if ($preValue !== NULL && !\Pimcore::inAdmin()) {
            return $preValue;
        }

        $data = $this->getClass()->getFieldDefinition('localizedfields')->preGetData($this);
        return $data;
    }

    /**
     * Get first level of categories

     * @return $this
     */
    public function getFirstLevel()
    {
        $mostTop = $this->getHierarchy();
        return $mostTop[0];
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
        $allChildren = [$category->getId()];

        $loopChildren = function (Category $child) use (&$loopChildren, &$allChildren) {
            $children = $child->getChildCategories();

            foreach ($children as $child) {
                $allChildren[] = $child->getId();
                $loopChildren($child);
            }
        };

        $loopChildren($category);

        return $allChildren;
    }

    /**
     * Get News from the Category
     *
     * @param bool $includeChildCategories
     *
     * @return array
     */
    public function getEntries($includeChildCategories = FALSE)
    {
        $list = Object\NewsCategory::getList();

        if (!$includeChildCategories) {
            $list->setCondition('enabled = 1 AND categories LIKE "%,' . $this->getId() . ',%"');
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = [];

            foreach ($categories as $cat) {
                $categoriesWhere[] = 'categories LIKE ",' . $cat . ',%"';
            }

            $list->setCondition('enabled = 1 AND (' . implode(' OR ', $categoriesWhere) . ')');
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
    public function getEntriesPaging(
        $page = 0,
        $itemsPerPage = 10,
        $sort = [
            'name'      => 'name',
            'direction' => 'asc'
        ],
        $includeChildCategories = FALSE
    ) {
        $list = Object\NewsCategory::getList();

        if (!$includeChildCategories) {
            $list->setCondition("enabled = 1 AND categories LIKE '%," . $this->getId() . ",%'");
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = [];

            foreach ($categories as $cat) {
                $categoriesWhere[] = "categories LIKE '%," . $cat . ",%'";
            }

            $list->setCondition('enabled = 1 AND (' . implode(' OR ', $categoriesWhere) . ')');
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
     * @param int      $level to check hierarchy (0 = topMost)
     *
     * @return bool
     */
    public function inCategory(Category $category, $level = 0)
    {
        $mostTop = $this->getHierarchy();
        $mostTop = $mostTop[$level];

        $children = self::getAllChildCategories($mostTop);
        return in_array($category->getId(), $children);
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
        $hierarchy = [];

        $category = $this;

        do {
            $hierarchy[] = $category;
            $category = $category->getParent();
        } while ($category instanceof Category);

        return array_reverse($hierarchy);
    }

    /**
     * Get all child Categories
     * @return array
     */
    public function getChildCategories()
    {
        $list = Object\NewsCategory::getList();
        $list->setCondition('o_parentId = ?', [$this->getId()]);

        return $list->getObjects();
    }
}
