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

use NewsBundle\Generator\RelatedEntriesGeneratorInterface;
use NewsBundle\Model\EntryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RelatedEntriesExtension extends AbstractExtension
{
    private RelatedEntriesGeneratorInterface $relatedEntriesGenerator;

    public function __construct(RelatedEntriesGeneratorInterface $relatedEntriesGenerator)
    {
        $this->relatedEntriesGenerator = $relatedEntriesGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('news_related_entries', [$this, 'generateRelatedEntries']),
        ];
    }

    public function generateRelatedEntries(EntryInterface $entry, array $params = []): array
    {
        return $this->relatedEntriesGenerator->generateRelatedEntries($entry, $params);
    }
}
