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

use Doctrine\DBAL\Query\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use NewsBundle\Exception\ImplementedByPimcoreException;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject;

class Entry extends DataObject\Concrete implements EntryInterface
{
    public static function getAll(): array
    {
        $newsListing = DataObject\NewsEntry::getList();
        static::modifyListing($newsListing);

        return $newsListing->getObjects();
    }

    public static function getEntriesPaging(array $params = []): PaginationInterface
    {
        $settings = array_merge([
            'sort'                 => [
                'field' => 'date',
                'dir'   => 'desc'
            ],
            'page'                 => 0,
            'offset'               => 0,
            'itemsPerPage'         => 10,
            'entryType'            => 'all',
            'timeRange'            => 'all',
            'category'             => null,
            'onlyLatest'           => false,
            'includeSubCategories' => false,
            'singleObjects'        => [],
            'where'                => [],
            'request'              => []
        ], $params);

        $newsListing = DataObject\NewsEntry::getList();
        $newsListing->setOrderKey($settings['sort']['field']);
        $newsListing->setOrder($settings['sort']['dir']);
        $newsListing->setGroupBy('id');

        $paginator = \Pimcore::getContainer()->get('knp_paginator');

        $categories = null;
        if (isset($settings['category']) && $settings['category'] instanceof Category) {
            $categories = static::getCategoriesRecursive($settings['category'], $settings['includeSubCategories']);
        }

        //add optional category selector
        static::addCategorySelectorToQuery($newsListing, $categories, $settings);

        //add timeRange
        static::addTimeRange($newsListing, $settings);

        //add single entry types
        if (count($settings['singleObjects']) > 0) {
            $newsListing->addConditionParam(
                sprintf('oo_id IN(%s)', implode(',', array_map(static function ($object) {
                    return $object->getId();
                }, $settings['singleObjects'])))
            );
        }

        //add entry type selector
        if ($settings['entryType'] !== 'all') {
            $newsListing->addConditionParam('entryType = ?', $settings['entryType']);
        }

        //show only latest
        if ($settings['onlyLatest'] === true) {
            $newsListing->addConditionParam('latest = ?', 1);
        }

        //add additional where clauses
        if (count($settings['where']) > 0) {
            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }
        }

        //add offset
        if (is_numeric($settings['offset']) && $settings['offset'] > 0) {
            $newsListing->setOffset($settings['offset']);
        }

        //do not allow empty names
        $newsListing->addConditionParam('name <> ?', '');

        //allow listing modification.
        static::modifyListing($newsListing, $settings);

        if ($newsListing->getOffset()) {
            $notList = clone $newsListing;
            $notList->setOffset(0);
            $notList->setLimit($newsListing->getOffset());
            $notIdList = $notList->loadIdList();
            $newsListing->filterById([$notIdList], 'NOT IN(?)');
        }

        return $paginator->paginate(
            $newsListing,
            $settings['page'] === 0 ? 1 : $settings['page'],
            $settings['itemsPerPage'] === 0 ? 10 : $settings['itemsPerPage']
        );
    }

    public static function addTimeRange(DataObject\NewsEntry\Listing $newsListing, array $settings = []): void
    {
        if (empty($settings['timeRange']) || $settings['timeRange'] === 'all') {
            return;
        }

        $identifier = '>=';
        if ($settings['timeRange'] === 'past') {
            $identifier = '<';
        }

        $newsListing->addConditionParam(
            sprintf('(
            CASE WHEN showEntryUntil IS NOT NULL
                THEN
                    showEntryUntil %1$s UNIX_TIMESTAMP(NOW())
                ELSE
                    (CASE WHEN dateTo IS NOT NULL
                        THEN
                            dateTo %1$s UNIX_TIMESTAMP(NOW())
                        ELSE
                            (CASE WHEN date IS NOT NULL
                                THEN
                                    date %1$s UNIX_TIMESTAMP(NOW())
                                ELSE
                                    FALSE
                                END
                            )
                        END
                    )
                END
            )
        ', $identifier)
        );
    }

    public static function addCategorySelectorToQuery(DataObject\Listing\Concrete $newsListing, ?array $categories = null, array $settings = []): void
    {
        $newsListing->onCreateQueryBuilder(function (QueryBuilder $query) use ($newsListing, $categories, $settings) {
            if (!empty($categories)) {
                $aliasFrom = $newsListing->getDao()->getTableName();
                $query->join($aliasFrom, 'object_relations_' . $newsListing->getClassId(), 'relations', 'relations.src_id = oo_id');
            }

            //allow query modification.
            static::modifyQuery($query, $newsListing, $settings);
        });

        if (!empty($categories)) {
            $newsListing->addConditionParam(
                'relations.fieldname = "categories" AND relations.dest_id IN (' . rtrim(str_repeat('?,', count($categories)), ',') . ')',
                $categories
            );
        }
    }

    protected static function modifyQuery(QueryBuilder $query, DataObject\Listing\Concrete $listing, array $settings = []): void
    {
    }

    protected static function modifyListing(DataObject\Listing\Concrete $listing, array $settings = []): void
    {
    }

    /**
     * @throws \Exception
     */
    public static function getCategoriesRecursive(?CategoryInterface $category, $includeSubCategories = false): ?array
    {
        if (!$category instanceof DataObject\AbstractObject) {
            return null;
        }

        $categories = [$category->getId()];
        if ($includeSubCategories === true) {
            $entries = DataObject\NewsCategory::getList();
            $entries->setCondition('path LIKE "' . $category->getFullPath() . '%"');
            foreach ($entries->load() as $entry) {
                $categories[] = $entry->getId();
            }
        }

        return array_values($categories);
    }

    public function getImage(): ?Asset
    {
        $images = $this->getImages();
        if (count($images) > 0) {
            return $images[0];
        }

        return null;
    }

    public function getJsonLDData(): array
    {
        $thumbnailDefinition = \Pimcore::getContainer()->getParameter('image_thumbnails');
        $galleryImageThumbnailName = $thumbnailDefinition['gallery_image'];

        $data = [
            '@context'      => 'http://schema.org/',
            '@type'         => 'NewsArticle',
            'datePublished' => $this->getDate()->format('Y-m-d'),
            'headline'      => $this->getName(),
            'description'   => $this->getLead(),
            'articleBody'   => $this->getDescription()
        ];

        if ($this->getAuthor()) {
            $data['author'] = $this->getAuthor();
        }

        $images = $this->getImages();
        if (count($images) > 0) {
            $image = $images[0];
            if ($image instanceof Image) {
                $data['image'] = [
                    '@type'  => 'ImageObject',
                    'url'    => \Pimcore\Tool::getHostUrl() . $image->getThumbnail($galleryImageThumbnailName)->getPath(),
                    'width'  => $image->getThumbnail($galleryImageThumbnailName)->getWidth(),
                    'height' => $image->getThumbnail($galleryImageThumbnailName)->getHeight(),
                ];
            }
        }

        return $data;
    }

    public function getName(?string $language = null)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getLead(?string $language = null)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getDescription(?string $language = null)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getRedirectLink(?string $language = null)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getDetailUrl(?string $language = null)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getEntryType()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getCategories()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getMetaTitle()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    public function getMetaDescription()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }
}
