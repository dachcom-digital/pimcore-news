<?php

namespace NewsBundle\Twig\Extension;

use NewsBundle\Generator\LinkGenerator;
use NewsBundle\Model\EntryInterface;

class NewsExtension extends \Twig_Extension
{
    /**
     * @var LinkGenerator
     */
    private $linkGenerator;

    /**
     * NewsExtension constructor.
     *
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(LinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * Returns a list of functions to add to the existing list.
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'news_entry_permalink',
                [$this, 'generatePermalink']
            ),
            new \Twig_SimpleFunction(
                'news_entry_backlink',
                [$this, 'generateBackLink']
            ),
        ];
    }

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generatePermalink(EntryInterface $entry)
    {
        return $this->linkGenerator->generateDetailLink($entry);
    }

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateBackLink(EntryInterface $entry)
    {
        return $this->linkGenerator->generateBackLink($entry);
    }
}