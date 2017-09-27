<?php

namespace NewsBundle\Generator;

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
     * @param       $entry
     * @param array $additionalUrlParams
     *
     * @return string
     */
    public function generateDetailLink(EntryInterface $entry, $additionalUrlParams = [])
    {
        $href = NULL;
        $isRedirectLink = FALSE;

        if ($entry->getRedirectLink() instanceof Document) {
            $href = $entry->getRedirectLink()->getFullPath();
            $isRedirectLink = TRUE;
        }

        $eventParams = [];

        if (is_null($href)) {
            $staticRouteInfo = $this->entryTypeManager->getRouteInfo($entry->getEntryType());

            $params = array_merge([
                'entry' => $entry->getDetailUrl()
            ], $additionalUrlParams);

            $href = $this->urlGenerator->generate($staticRouteInfo['name'], $params);

            $eventParams['staticRouteName'] = $staticRouteInfo['name'];
            $eventParams['routeParams'] = $params;
        }

        $absPath = Tool::getHostUrl() . $href;

        $eventParams['url'] = $absPath;
        $eventParams['isRedirectLink'] = $isRedirectLink;

        return $absPath;
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
                && strpos($_SERVER['HTTP_REFERER'], Tool::getHostUrl()) !== FALSE
            ) {
                $backLink = $_SERVER['HTTP_REFERER'];
            }
        }

        return $backLink;
    }
}