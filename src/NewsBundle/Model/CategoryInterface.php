<?php

namespace NewsBundle\Model;

use Knp\Component\Pager\PaginatorInterface;

interface CategoryInterface
{
    /**
     * @throws \Exception
     */
    public static function getAll(): array;

    public function getFirstLevel(): CategoryInterface;

    public static function getAllChildCategories(Category $category): array;

    /**
     * @throws \Exception
     */
    public function getEntries(bool $includeChildCategories = false): array;

    public function getEntriesPaging(
        $page = 0,
        $itemsPerPage = 10,
        $sort = [
            'name'      => 'name',
            'direction' => 'asc'
        ],
        $includeChildCategories = false
    ): PaginatorInterface;

    public function inCategory(Category $category, int $level = 0): bool;

    public function getLevel(): int;

    public function getCatChildren(): array;

    public function getHierarchy(): array;

    public function getChildCategories(): array;
}
