<?php

namespace NewsBundle\Generator;

use NewsBundle\Model\EntryInterface;

interface HeadMetaGeneratorInterface
{
    /**
     * @return string
     */
    public function getTitlePosition();

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateTitle(EntryInterface $entry);

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateDescription(EntryInterface $entry);

    /**
     * @param EntryInterface $entry
     *
     * @return array
     */
    public function generateMeta(EntryInterface $entry): array;

}