<?php

namespace NewsBundle\Generator;

use NewsBundle\Model\EntryInterface;
use Pimcore\Model\Object\NewsEntry;

interface RelatedEntriesGeneratorInterface
{
    /**
     * @param EntryInterface $news
     * @param array          $params
     *
     * @return NewsEntry\Listing
     */
    public function generateRelatedEntries(EntryInterface $news, $params = []);
}