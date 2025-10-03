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

namespace NewsBundle\Generator;

use NewsBundle\Model\EntryInterface;
use Pimcore\Model\Asset;
use Pimcore\Tool;
use Pimcore\Twig\Extension\Templating\Placeholder\Container;

class HeadMetaGenerator implements HeadMetaGeneratorInterface
{
    protected LinkGeneratorInterface $linkGenerator;

    public function __construct(LinkGeneratorInterface $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    public function getTitlePosition(): string
    {
        return Container::PREPEND;
    }

    public function generateTitle(EntryInterface $entry): string
    {
        $mT = $entry->getMetaTitle();

        return !empty($mT) ? $mT : $entry->getName();
    }

    public function generateDescription(EntryInterface $entry): string
    {
        $mD = $entry->getMetaDescription();
        $description = !empty($mD) ? $mD : ($entry->getLead() ? $entry->getLead() : $entry->getDescription());

        if ($description === null) {
            return '';
        }

        return trim(substr($description, 0, 160));
    }

    public function generateMeta(EntryInterface $entry): array
    {
        $title = $this->generateTitle($entry);
        $description = $this->generateDescription($entry);

        $href = $this->linkGenerator->generateDetailLink($entry);

        $ogTitle = $title;
        $ogDescription = $description;
        $ogUrl = $href;
        $ogType = 'article';

        $ogImage = null;

        if ($entry->getImage() instanceof Asset\Image) {
            $ogImage = Tool::getHostUrl() . $entry->getImage()->getThumbnail('contentImage');
        }

        return [
            'og:title'       => $ogTitle,
            'og:description' => $ogDescription,
            'og:url'         => $ogUrl,
            'og:image'       => $ogImage,
            'og:type'        => $ogType
        ];
    }
}
