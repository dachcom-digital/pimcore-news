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

use Knp\Component\Pager\Pagination\PaginationInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;

interface EntryInterface
{
    /**
     * @throws \Exception
     */
    public static function getAll(): array;

    public static function getEntriesPaging(array $params = []): PaginationInterface;

    public static function addCategorySelectorToQuery(DataObject\Listing\Concrete $newsListing, ?array $categories = null, array $settings = []): void;

    public static function getCategoriesRecursive(?CategoryInterface $category, bool $includeSubCategories = false): ?array;

    public function getImage(): ?Asset;

    public function getName(?string $language = null);

    public function getLead(?string $language = null);

    public function getDescription(?string $language = null);

    public function getJsonLDData();

    public function getEntryType();

    public function getRedirectLink(?string $language = null);

    public function getDetailUrl(?string $language = null);

    public function getCategories();

    public function getMetaTitle();

    public function getMetaDescription();
}
