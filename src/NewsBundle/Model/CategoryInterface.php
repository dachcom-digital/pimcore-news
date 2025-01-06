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

interface CategoryInterface
{
    /**
     * @throws \Exception
     */
    public static function getAll(): array;

    public function getFirstLevel(): self;

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
