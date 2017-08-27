<?php

namespace NewsBundle\Twig\Extension;

use NewsBundle\Generator\RelatedEntriesGeneratorInterface;
use NewsBundle\Model\EntryInterface;

class RelatedEntriesExtension extends \Twig_Extension
{
    /**
     * @var RelatedEntriesGeneratorInterface
     */
    private $relatedEntriesGenerator;

    /**
     * @param RelatedEntriesGeneratorInterface  $relatedEntriesGenerator
     */
    public function __construct(RelatedEntriesGeneratorInterface $relatedEntriesGenerator)
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
            new \Twig_Function(
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