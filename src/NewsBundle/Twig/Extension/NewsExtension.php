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

namespace NewsBundle\Twig\Extension;

use NewsBundle\Generator\LinkGeneratorInterface;
use NewsBundle\Model\EntryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NewsExtension extends AbstractExtension
{
    private LinkGeneratorInterface $linkGenerator;
    private array $imageThumbnails;

    public function __construct(LinkGeneratorInterface $linkGenerator, array $imageThumbnails)
    {
        $this->linkGenerator = $linkGenerator;
        $this->imageThumbnails = $imageThumbnails;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('news_entry_permalink', [$this, 'generatePermalink']),
            new TwigFunction('news_entry_backlink', [$this, 'generateBackLink']),
            new TwigFunction('news_thumbnail', [$this, 'getNewsThumbnail'])
        ];
    }

    public function generatePermalink(EntryInterface $entry): string
    {
        return $this->linkGenerator->generateDetailLink($entry);
    }

    public function generateBackLink(EntryInterface $entry): string
    {
        return $this->linkGenerator->generateBackLink($entry);
    }

    public function getNewsThumbnail(string $thumbnail): string
    {
        return array_key_exists($thumbnail, $this->imageThumbnails) ? $this->imageThumbnails[$thumbnail] : '';
    }
}
