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

namespace NewsBundle\EventListener;

use NewsBundle\Configuration\Configuration;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\DataObject\NewsCategory;
use Pimcore\Model\DataObject\NewsEntry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class NewsSeoListener implements EventSubscriberInterface
{
    protected RequestStack $requestStack;
    protected Configuration $configuration;

    public function __construct(
        RequestStack $requestStack,
        Configuration $configuration
    ) {
        $this->requestStack = $requestStack;
        $this->configuration = $configuration;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_ADD    => 'handleObjectSeoAdd',
            DataObjectEvents::PRE_UPDATE => 'handleObjectSeoUpdate'
        ];
    }

    public function handleObjectSeoAdd(DataObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof NewsEntry && !$object instanceof NewsCategory) {
            return;
        }

        $reset = false;
        $masterRequest = $this->requestStack->getMainRequest();
        if ($masterRequest instanceof Request) {
            $reset = true;
            foreach (['sourceId', 'targetId', 'transactionId'] as $copyTransactionArgument) {
                if ($masterRequest->request->has($copyTransactionArgument) === false) {
                    $reset = false;

                    break;
                }
            }
        }

        if ($reset === true) {
            //always reset stored url and unpublish element if element just has been copied.
            $object->setPublished(false);
            $languages = \Pimcore\Tool::getValidLanguages();
            foreach ($languages as $language) {
                $object->setDetailUrl(null, $language);
            }

            return;
        }

        $this->parseUrl($object);
    }

    public function handleObjectSeoUpdate(DataObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof NewsEntry && !$object instanceof NewsCategory) {
            return;
        }

        $this->parseUrl($object);
    }

    private function parseUrl($object): void
    {
        $languages = \Pimcore\Tool::getValidLanguages();

        /** @var AbstractObject $objectClass */
        $objectClass = get_class($object);
        $objectListingClass = sprintf('%s\Listing', $objectClass);

        $oldObject = null;
        if ($object->getId() > 0) {
            $oldObject = $objectClass::getById($object->getId(), ['force' => true]);
        }

        foreach ($languages as $language) {
            $oldDetailUrl = null;

            $title = $object->getName($language);
            $currentDetailUrl = $object->getDetailUrl($language);

            if ($oldObject instanceof $objectClass) {
                $oldDetailUrl = $oldObject->getDetailUrl($language);
            }

            // title and detail url is empty, nothing we can do
            if (empty($title) && empty($currentDetailUrl)) {
                continue;
            }

            // title is empty but detail url has been set: not allowed
            if (empty($title) && !empty($currentDetailUrl)) {
                $currentDetailUrl = null;
                $object->setDetailUrl($currentDetailUrl, $language);

                return;
            }

            // title is not empty but we have no detail url: build it!
            if (!empty($title) && empty($currentDetailUrl)) {
                $currentDetailUrl = $this->slugify($title, $language);
            }

            // current and last given detail url haven't changed: skip
            if ($oldDetailUrl === $currentDetailUrl) {
                continue;
            }

            // ensure current detail url has a valid slug
            $currentDetailUrl = $this->slugify($currentDetailUrl, $language);

            if ($this->otherElementsExists($objectListingClass, $language, $currentDetailUrl, $object->getId()) === false) {
                $object->setDetailUrl($currentDetailUrl, $language);

                continue;
            }

            $version = 1;
            $originalUrl = $currentDetailUrl;
            $nextLevelDetailUrl = sprintf('%s-%d', $originalUrl, $version);

            while ($this->otherElementsExists($objectListingClass, $language, $nextLevelDetailUrl, $object->getId()) !== false) {
                $nextLevelDetailUrl = sprintf('%s-%d', $originalUrl, ++$version);
            }

            $object->setDetailUrl($nextLevelDetailUrl, $language);
        }
    }

    protected function slugify(string $string, ?string $language): string
    {
        //remove dashes first
        $string = preg_replace('/[-\s]+/', ' ', $string);

        if ($language === 'de') {
            $string = preg_replace(['/ä/i', '/ö/i', '/ü/i', '/ß/'], ['ae', 'oe', 'ue', 'ss'], $string);
        }

        $string = preg_replace(['/®/', '/©/'], '', $string);
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();', $string);
        // Remove repeating hyphens and spaces (e.g. 'foo---bar' becomes 'foo-bar')
        $string = preg_replace('/[-\s]+/', '-', $string);

        return trim($string, '-');
    }

    protected function otherElementsExists(string $objectListingClass, string $locale, string $detailUrl, ?int $id): bool
    {
        /** @var Listing $listing */
        $listing = new $objectListingClass();
        $listing->setUnpublished(true);
        $listing->setLocale($locale);
        $listing->setLimit(1);
        $listing->addConditionParam('detailUrl = ?', $detailUrl);

        if (!is_null($id)) {
            $listing->addConditionParam('ooo_id != ?', $id);
        }

        return $listing->getCount() > 0;
    }
}
