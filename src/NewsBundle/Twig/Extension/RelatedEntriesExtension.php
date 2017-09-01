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
     * @param RelatedEntriesGeneratorInterface $relatedEntriesGenerator
     */
    public function __construct(RelatedEntriesGeneratorInterface $relatedEntriesGenerator)
    {
        $this->relatedEntriesGenerator = $relatedEntriesGenerator;
    }

    /**
     * Returns a list of functions to add to the existing list.
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function(
                'news_related_entries',
                [$this, 'generateRelatedEntries']
            ),
        ];
    }

    /**
     * @param EntryInterface $entry
     * @param array          $params
     *
     * @return string
     */
    public function generateRelatedEntries(EntryInterface $entry, $params = [])
    {
        return $this->relatedEntriesGenerator->generateRelatedEntries($entry, $params);
    }
}