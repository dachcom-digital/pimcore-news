<?php

namespace NewsBundle\Model;

use Pimcore\Model\DataObject;
use Zend\Paginator\Paginator;

class Category extends DataObject\Concrete implements CategoryInterface
{
    /**
     * Get all categories
     * @return array
     * @throws \Exception
     */
    public static function getAll()
    {
        $list = DataObject\NewsCategory::getList();
        return $list->getObjects();
    }

    /**
     * Get localized fields
     *
     * @return array
     */
    public function getLocalizedFields()
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
     *
     * @return $this
     */
    public function getFirstLevel()
    {
        $mostTop = $this->getHierarchy();
        return $mostTop[0];
    }

    /**
     * Returns all child categories from $category
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
     * Get news from the category
     *
     * @param bool $includeChildCategories
     * @return array
     * @throws \Exception
     */
    public function getEntries($includeChildCategories = false)
    {
        $list = DataObject\NewsEntry::getList();

        if (!$includeChildCategories) {
            $list->setCondition('o_published = 1 AND categories LIKE "%,' . $this->getId() . ',%"');
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = [];

            foreach ($categories as $cat) {
                $categoriesWhere[] = 'categories LIKE ",' . $cat . ',%"';
            }

            $list->setCondition('o_published = 1 AND (' . implode(' OR ', $categoriesWhere) . ')');
        }

        return $list->getObjects();
    }

    /**
     * Get paged entries from category
     *
     * @param int   $page
     * @param int   $itemsPerPage
     * @param array $sort
     * @param bool  $includeChildCategories
     * @return Paginator
     * @throws \Exception
     */
    public function getEntriesPaging(
        $page = 0,
        $itemsPerPage = 10,
        $sort = [
            'name'      => 'name',
            'direction' => 'asc'
        ],
        $includeChildCategories = false
    ) {
        $list = DataObject\NewsEntry::getList();

        if (!$includeChildCategories) {
            $list->setCondition('o_published = 1 AND categories LIKE "%,' . $this->getId() . ',%"');
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = [];

            foreach ($categories as $cat) {
                $categoriesWhere[] = 'categories LIKE "%,' . $cat . ',%"';
            }

            $list->setCondition('o_published = 1 AND (' . implode(' OR ', $categoriesWhere) . ')');
        }

        $list->setOrderKey($sort['name']);
        $list->setOrder($sort['direction']);

        $paginator = new Paginator($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsPerPage);

        return $paginator;
    }

    /**
     * Checks if category is child of hierarchy
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
     * Get level of category
     *
     * @return int
     */
    public function getLevel()
    {
        return count($this->getHierarchy());
    }

    /**
     * Returns all children from this category
     *
     * @return array
     */
    public function getCatChildren()
    {
        return self::getAllChildCategories($this);
    }

    /**
     * Get category hierarchy
     *
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
     * Get all child categories
     *
     * @return array
     * @throws \Exception
     */
    public function getChildCategories()
    {
        $list = DataObject\NewsCategory::getList();
        $list->setCondition('o_parentId = ?', [$this->getId()]);

        return $list->getObjects();
    }
}
