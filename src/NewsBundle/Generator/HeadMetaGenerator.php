<?php

namespace NewsBundle\Generator;

use NewsBundle\Model\EntryInterface;
use Pimcore\Model\Asset;
use Pimcore\Templating\Helper\Placeholder\Container;
use Pimcore\Tool;

class HeadMetaGenerator
{
    const TITLE_POSITION = Container::PREPEND;

    /**
     * @var LinkGenerator
     */
    protected $linkGenerator;

    /**
     * HeadMetaGenerator constructor.
     *
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(LinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateTitle(EntryInterface $entry)
    {
        $mT = $entry->getMetaTitle();
        $title = !empty($mT) ? $mT : $entry->getName();

        return $title;
    }

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateDescription(EntryInterface $entry)
    {
        $mD = $entry->getMetaDescription();
        $description = !empty($mD) ? $mD : ($entry->getLead() ? $entry->getLead() : $entry->getDescription());
        $description = trim(substr($description, 0, 160));

        return $description;
    }

    /**
     * @param EntryInterface $entry
     *
     * @return array
     */
    public function generateMeta(EntryInterface $entry): array
    {
        $title = $this->generateTitle($entry);
        $description = $this->generateDescription($entry);

        $href = $this->linkGenerator->generateDetailLink($entry);

        $ogTitle = $title;
        $ogDescription = $description;
        $ogUrl = $href;
        $ogType = 'article';

        $ogImage = NULL;

        if ($entry->getImage() instanceof Asset\Image) {
            $ogImage = Tool::getHostUrl() . $entry->getImage()->getThumbnail('contentImage');
        }

        $params = [
            'og:title'       => $ogTitle,
            'og:description' => $ogDescription,
            'og:url'         => $ogUrl,
            'og:image'       => $ogImage,
            'og:type'        => $ogType
        ];

        return $params;
    }

}