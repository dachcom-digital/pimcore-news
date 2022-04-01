<?php

namespace NewsBundle\Generator;

use NewsBundle\Model\EntryInterface;
use Pimcore\Model\Asset;
use Pimcore\Twig\Extension\Templating\Placeholder\Container;
use Pimcore\Tool;

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
