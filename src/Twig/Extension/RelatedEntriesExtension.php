<?php

namespace NewsBundle\Twig\Extension;

use NewsBundle\Generator\RelatedEntriesGenerator;
use NewsBundle\Model\EntryInterface;

class RelatedEntriesExtension extends \Twig_Extension
{
    /**
     * @var RelatedEntriesGenerator
     */
    private $relatedEntriesGenerator;

    /**
     * @param RelatedEntriesGenerator  $relatedEntriesGenerator
     */
    public function __construct(RelatedEntriesGenerator $relatedEntriesGenerator)
    {
        $this->relatedEntriesGenerator = $relatedEntriesGenerator;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'news_related_entries',
                array($this, 'generateRelatedEntries')
            ),
        );
    }

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateRelatedEntries(EntryInterface $entry)
    {
        return $this->relatedEntriesGenerator->generateRelatedEntries($entry);
    }
}