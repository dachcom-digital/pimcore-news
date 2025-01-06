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

use NewsBundle\Manager\EntryTypeManager;
use NewsBundle\Model\EntryInterface;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Document;
use Pimcore\Tool;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LinkGenerator implements LinkGeneratorInterface
{
    protected EntryTypeManager $entryTypeManager;
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntryTypeManager $entryTypeManager)
    {
        $this->entryTypeManager = $entryTypeManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function generateDetailLink(EntryInterface $entry, $additionalUrlParams = []): string
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

    public function generateBackLink(EntryInterface $entry): string
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
