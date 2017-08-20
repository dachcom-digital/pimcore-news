<?php

namespace NewsBundle\Generator;

use NewsBundle\Model\Entry;
use NewsBundle\Model\EntryInterface;
use Pimcore\Model\Object;

class RelatedEntriesGenerator
{
    /**
     * @param EntryInterface $news
     * @param array          $params
     *
     * @return mixed
     */
    public function generateRelatedEntries(EntryInterface $news, $params = [])
    {
        $settings = array_merge([
            'sort'                 => [
                'field' => 'date',
                'dir'   => 'desc'
            ],
            'limit'                => 4,
            'where'                => [],
            'entryType'            => $news->getEntryType(),
            'includeSubCategories' => FALSE,
            'ignoreCategory'       => FALSE

        ], $params);

        $newsListing = Object\NewsEntry::getList([
            'limit' => $settings['limit']
        ]);
        if (is_string($settings['sort']) && strtolower($settings['sort']) === 'random') {
            $newsListing->setOrderKey('RAND()', FALSE);
        } else {
            $newsListing->setOrderKey($settings['sort']['field']);
            $newsListing->setOrder($settings['sort']['dir']);
        }

        $newsListing->addConditionParam('name <> ""');
        $newsListing->setGroupBy('o_id');

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
        $newsListing->addConditionParam('o_id != ?', $news->getId());

        //add additional where clauses.
        if (count($settings['where'])) {
            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }
        }

        return $newsListing->load();
    }

}