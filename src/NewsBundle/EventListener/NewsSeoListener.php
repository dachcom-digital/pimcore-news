<?php

namespace NewsBundle\EventListener;

use NewsBundle\Configuration\Configuration;
use Pimcore\Cache;
use Pimcore\Db;
use Pimcore\Event\Model\ObjectEvent;
use Pimcore\Event\ObjectEvents;
use Pimcore\Model\Object\News;
use Pimcore\Model\Version;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsSeoListener implements EventSubscriberInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * RestrictionServiceListener constructor.
     *
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
            ObjectEvents::PRE_ADD    => 'handleObjectSeo',
            ObjectEvents::PRE_UPDATE => 'handleObjectSeo'
        ];
    }

    /**
     * @param ObjectEvent $e
     */
    public function handleObjectSeo(ObjectEvent $e)
    {
        $newsObject = $e->getObject();
        $className = (new \ReflectionClass($newsObject))->getShortName();

        if (!in_array($className, ['NewsEntry', 'NewsCategory'])) {
            return;
        }

        Version::disable();
        $this->parseUrl($newsObject, $className);
        Version::enable();

        Cache::clearTag('object_' . $newsObject->getId());
    }

    /**
     * @param \Pimcore\Model\Object           $object
     * @param string (NewsEntry|NewsCategory) $className
     *
     * @return bool
     * @throws \Exception
     * @throws \Pimcore\Model\Element\ValidationException
     */
    private function parseUrl($object, $className = '')
    {
        $languages = \Pimcore\Tool::getValidLanguages();

        $fromCopy = FALSE;

        $objectClass = 'Pimcore\\Model\\Object\\' . $className;
        foreach ($languages as $language) {
            if ($this->isFromCopy($object, $objectClass, $language) === TRUE) {
                $fromCopy = TRUE;
                break;
            }
        }

        if ($fromCopy) {
            foreach ($languages as $language) {
                //always reset stored url if element just has been copied.
                $object->setDetailUrl('', $language);
            }
        }

        $oldObject = NULL;
        if ($object->getId()) {

            //@todo: use versions?
            $oldObject = \Pimcore::getContainer()->get('pimcore.model.factory')->build($objectClass);
            $oldObject->getDao()->getById($object->getId());
        }

        foreach ($languages as $language) {
            $realUrl = '';

            $currentDetailUrl = $object->getDetailUrl($language);
            $title = $object->getName($language);

            //skip all empty!
            if (empty($title) && empty($currentDetailUrl)) {
                continue;
            }

            if (!empty($title)) {
                $realUrl = $this->slugify(empty($currentDetailUrl) ? $title : $currentDetailUrl, $language);
                if (empty($currentDetailUrl)) {
                    $currentDetailUrl = $realUrl;
                }
            }

            $versionUrl = $realUrl;

            $oldDetailUrl = NULL;
            if ($oldObject instanceof $objectClass) {
                $oldDetailUrl = $oldObject->getDetailUrl($language);
            }

            if ($oldDetailUrl !== $currentDetailUrl) {
                $sameUrlObject = $objectClass::getByLocalizedfields(
                    'detailUrl', $realUrl, $language,
                    ['limit' => 1, 'condition' => ' AND ooo_id != ' . (int)$object->getId() . ' AND name <> ""']
                );

                if (count($sameUrlObject) === 1) {
                    $nextPossibleVersion = $sameUrlObject;

                    $version = 1;
                    $versionUrl = $realUrl . '-' . ($version);

                    while ($nextPossibleVersion instanceof $objectClass) {
                        $versionUrl = $realUrl . '-' . ($version);

                        $nextPossibleVersion = $objectClass::getByLocalizedfields(
                            'detailUrl', $versionUrl, $language,
                            ['limit' => 1, 'condition' => ' AND ooo_id != ' . (int)$object->getId() . ' AND name <> ""']
                        );

                        $version++;
                    }
                }

                $object->setDetailUrl($versionUrl, $language);
            }
        }
    }

    /**
     * @param $obj
     * @param $objectClass
     * @param $language
     *
     * @return bool
     */
    private function isFromCopy($obj, $objectClass, $language)
    {
        //always check if there is double data!!!
        $name = $obj->getName($language);
        $url = $obj->getDetailUrl($language);

        if (empty($url)) {
            return FALSE;
        }

        $duplicateUrlObjects = $objectClass::getByLocalizedfields(
            'detailUrl', $url, $language,
            ['limit' => 1, 'condition' => ' AND name = "' . Db::get()->quote($name) . '" AND ooo_id != ' . (int)$obj->getId()]
        );

        return count($duplicateUrlObjects) === 1;
    }

    /**
     * @param $string
     * @param $language
     *
     * @return string
     */
    private function slugify($string, $language)
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
}