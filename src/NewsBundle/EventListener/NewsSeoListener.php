<?php

namespace NewsBundle\EventListener;

use Pimcore\Model\DataObject\Listing;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\NewsCategory;
use Pimcore\Model\DataObject\NewsEntry;
use NewsBundle\Configuration\Configuration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsSeoListener implements EventSubscriberInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DataObjectEvents::PRE_ADD    => 'handleObjectSeoAdd',
            DataObjectEvents::PRE_UPDATE => 'handleObjectSeoUpdate'
        ];
    }

    /**
     * @param DataObjectEvent $event
     */
    public function handleObjectSeoAdd(DataObjectEvent $event)
    {
        $object = $event->getObject();
        if (!$object instanceof NewsEntry && !$object instanceof NewsCategory) {
            return;
        }

        $languages = \Pimcore\Tool::getValidLanguages();
        foreach ($languages as $language) {
            //always reset stored url if element just has been copied.
            $object->setDetailUrl(null, $language);
        }

        // in case it's a copy!
        $object->setPublished(false);
    }

    /**
     * @param DataObjectEvent $event
     */
    public function handleObjectSeoUpdate(DataObjectEvent $event)
    {
        $object = $event->getObject();
        if (!$object instanceof NewsEntry && !$object instanceof NewsCategory) {
            return;
        }

        $this->parseUrl($object);
    }

    /**
     * @param AbstractObject $object
     */
    private function parseUrl($object)
    {
        $languages = \Pimcore\Tool::getValidLanguages();

        /** @var AbstractObject $objectClass */
        $objectClass = get_class($object);
        $objectListingClass = sprintf('%s\Listing', $objectClass);

        $oldObject = null;
        if ($object->getId() > 0) {
            $oldObject = $objectClass::getById($object->getId(), true);
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

    /**
     * @param $string
     * @param $language
     *
     * @return string
     */
    protected function slugify($string, $language)
    {
        //remove dashes first
        $string = preg_replace('/[-\s]+/', ' ', $string);

        if ($language === 'de') {
            $string = preg_replace(['/ä/i', '/ö/i', '/ü/i', '/ß/'], ['ae', 'oe', 'ue', 'ss'], $string);
        }

        $string = preg_replace(['/®/', '/©/'], '', $string);
        $string = transliterator_transliterate("Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
        // Remove repeating hyphens and spaces (e.g. 'foo---bar' becomes 'foo-bar')
        $string = preg_replace('/[-\s]+/', '-', $string);

        return trim($string, '-');
    }

    /**
     * @param string $objectListingClass
     * @param string $locale
     * @param string $detailUrl
     * @param int    $id
     *
     * @return bool
     */
    protected function otherElementsExists(string $objectListingClass, string $locale, string $detailUrl, int $id)
    {
        /** @var Listing $listing */
        $listing = new $objectListingClass();
        $listing->setUnpublished(true);
        $listing->setLocale($locale);
        $listing->setLimit(1);
        $listing->addConditionParam('detailUrl = ?', $detailUrl);
        $listing->addConditionParam('ooo_id != ?', $id);

        return $listing->getCount() > 0;
    }
}
