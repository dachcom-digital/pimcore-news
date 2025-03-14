<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace NewsBundle\Model;

use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Model\DataObject;

class Category extends DataObject\Concrete implements CategoryInterface
{
    public static function getAll(): array
    {
        $list = DataObject\NewsCategory::getList();

        return $list->getObjects();
    }

    public function getFirstLevel(): CategoryInterface
    {
        $mostTop = $this->getHierarchy();

        return $mostTop[0];
    }

    public static function getAllChildCategories(self $category): array
    {
        $allChildren = [$category->getId()];
        $loopChildren = static function (Category $child) use (&$loopChildren, &$allChildren) {
            foreach ($child->getChildCategories() as $subChild) {
                $allChildren[] = $subChild->getId();
                $loopChildren($subChild);
            }
        };

        $loopChildren($category);

        return $allChildren;
    }

    public function getEntries(bool $includeChildCategories = false): array
    {
        $list = DataObject\NewsEntry::getList();

        if (!$includeChildCategories) {
            $list->setCondition('published = 1 AND categories LIKE "%,' . $this->getId() . ',%"');
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = [];

            foreach ($categories as $cat) {
                $categoriesWhere[] = 'categories LIKE ",' . $cat . ',%"';
            }

            $list->setCondition('published = 1 AND (' . implode(' OR ', $categoriesWhere) . ')');
        }

        return $list->getObjects();
    }

    /**
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
    ): PaginatorInterface {
        $list = DataObject\NewsEntry::getList();

        if (!$includeChildCategories) {
            $list->setCondition('published = 1 AND categories LIKE "%,' . $this->getId() . ',%"');
        } else {
            $categories = $this->getCatChilds();
            $categoriesWhere = [];

            foreach ($categories as $cat) {
                $categoriesWhere[] = 'categories LIKE "%,' . $cat . ',%"';
            }

            $list->setCondition('published = 1 AND (' . implode(' OR ', $categoriesWhere) . ')');
        }

        $list->setOrderKey($sort['name']);
        $list->setOrder($sort['direction']);

        $paginator = \Pimcore::getContainer()->get('knp_paginator');

        return $paginator->paginate(
            $list,
            $page === 0 ? 1 : $page,
            $itemsPerPage
        );
    }

    public function inCategory(self $category, int $level = 0): bool
    {
        $mostTop = $this->getHierarchy();
        $mostTop = $mostTop[$level];

        return in_array($category->getId(), self::getAllChildCategories($mostTop), true);
    }

    public function getLevel(): int
    {
        return count($this->getHierarchy());
    }

    public function getCatChildren(): array
    {
        return self::getAllChildCategories($this);
    }

    public function getHierarchy(): array
    {
        $hierarchy = [];
        $category = $this;

        do {
            $hierarchy[] = $category;
            $category = $category->getParent();
        } while ($category instanceof CategoryInterface);

        return array_reverse($hierarchy);
    }

    /**
     * @throws \Exception
     */
    public function getChildCategories(): array
    {
        $list = DataObject\NewsCategory::getList();
        $list->setCondition('parentId = ?', [$this->getId()]);

        return $list->getObjects();
    }
}
