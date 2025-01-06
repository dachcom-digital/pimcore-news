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

namespace NewsBundle\Generator;

use NewsBundle\Model\Entry;
use NewsBundle\Model\EntryInterface;
use Pimcore\Model\DataObject;

class RelatedEntriesGenerator implements RelatedEntriesGeneratorInterface
{
    /**
     * @param EntryInterface $news
     * @param array          $params
     *
     * @return DataObject\NewsEntry[]|DataObject\NewsEntry\Listing
     *
     * @throws \Exception
     */
    public function generateRelatedEntries(EntryInterface $news, array $params = [])
    {
        $settings = array_merge([
            'sort'                 => [
                'field' => 'date',
                'dir'   => 'desc'
            ],
            'limit'                => 4,
            'where'                => [],
            'entryType'            => $news->getEntryType(),
            'includeSubCategories' => false,
            'ignoreCategory'       => false
        ], $params);

        /** @var DataObject\NewsEntry\Listing $newsListing */
        $newsListing = DataObject\NewsEntry::getList([
            'limit' => $settings['limit']
        ]);
        if (is_string($settings['sort']) && strtolower($settings['sort']) === 'random') {
            $newsListing->setOrderKey('RAND()', false);
        } else {
            $newsListing->setOrderKey($settings['sort']['field']);
            $newsListing->setOrder($settings['sort']['dir']);
        }

        $newsListing->addConditionParam('name <> ""');
        $newsListing->setGroupBy($newsListing->getDao()->getTableName() . '.id', false);

        $categories = [];
        if (count($news->getCategories()) > 0 && !$settings['ignoreCategory']) {
            foreach ($news->getCategories() as $category) {
                $categories += Entry::getCategoriesRecursive($category, $settings['includeSubCategories']);
            }

            Entry::addCategorySelectorToQuery($newsListing, $categories);
        }

        if ($settings['entryType'] !== 'all') {
            $newsListing->addConditionParam('entryType = ?', $settings['entryType']);
        }

        $newsListing->addConditionParam($newsListing->getDao()->getTableName() . '.id != ?', $news->getId());

        //add additional where clauses.
        if (count($settings['where'])) {
            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }
        }

        return $newsListing->load();
    }
}
