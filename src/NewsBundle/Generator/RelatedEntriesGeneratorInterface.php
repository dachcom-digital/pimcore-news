<?php

namespace NewsBundle\Generator;

use NewsBundle\Model\EntryInterface;

interface RelatedEntriesGeneratorInterface
{
    public function generateRelatedEntries(EntryInterface $news, array $params = []);
}