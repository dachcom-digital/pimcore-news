<?php

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
