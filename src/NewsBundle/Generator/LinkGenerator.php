<?php

namespace NewsBundle\Generator;

use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use NewsBundle\Manager\EntryTypeManager;
use NewsBundle\Model\EntryInterface;
use Pimcore\Model\Document;
use Pimcore\Tool;

class LinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var EntryTypeManager
     */
    protected $entryTypeManager;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * LinkGenerator constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param EntryTypeManager      $entryTypeManager
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EntryTypeManager $entryTypeManager)
    {
        $this->entryTypeManager = $entryTypeManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param EntryInterface $entry
     * @param array          $additionalUrlParams
     *
     * @return string
     */
    public function generateDetailLink(EntryInterface $entry, $additionalUrlParams = [])
    {
        $path = null;
        $defaultParams = [
            'entry' => $entry->getDetailUrl()
        ];

        if ($entry->getRedirectLink() instanceof Document) {
            $path = $entry->getRedirectLink()->getFullPath();
            return Tool::getHostUrl() . $path;
        }

        $staticRouteInfo = $this->entryTypeManager->getRouteInfo($entry->getEntryType());

        /** @var ClassDefinition\LinkGeneratorInterface $linkGenerator */
        if ($linkGenerator = $entry->getClass()->getLinkGenerator()) {
            $lgParams = array_merge($additionalUrlParams, $linkGeneratorParams = [
                'document'        => null,
                'context'         => $this,
                'staticRouteInfo' => $staticRouteInfo
            ]);

            return $linkGenerator->generate($entry, $lgParams);
        }

        if ($staticRouteInfo['site'] !== null) {
            $defaultParams['site'] = $staticRouteInfo['site'];
        }

        $params = array_merge($defaultParams, $additionalUrlParams);

        $path = $this->urlGenerator->generate($staticRouteInfo['name'], $params);

        return Tool::getHostUrl() . $path;
    }

    /**
     * @param EntryInterface $entry
     *
     * @return string
     */
    public function generateBackLink(EntryInterface $entry)
    {
        $categories = $entry->getCategories();
        $backLink = '';
        if (count($categories) > 0) {
            $backLinkPage = $categories[0]->getBackLinkTarget();
            if ($backLinkPage instanceof Document\Page) {
                $backLink = $backLinkPage->getFullPath();
            }
        }

        if (empty($backLink)) {
            if (
                isset($_SERVER['HTTP_REFERER'])
                && preg_match('@^[^/]+://[^/]+@', $_SERVER['HTTP_REFERER'])
                && strpos($_SERVER['HTTP_REFERER'], Tool::getHostUrl()) !== false
            ) {
                $backLink = $_SERVER['HTTP_REFERER'];
            }
        }

        return $backLink;
    }
}