<?php

namespace News\Tool;

use Pimcore\Model\Site;
use Pimcore\Model\Staticroute;
use Pimcore\Model\Object;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Translate\Admin as TranslateAdapter;
use News\Model\Configuration;

class NewsTypes
{
    /**
     * @var array
     */
    protected static $routeData = [];

    /**
     * @param null $object
     *
     * @return array|mixed|null
     */
    public static function getTypes($object = NULL)
    {
        $newsTypes = static::getTypesFromConfig();

        $validLayouts = NULL;
        $masterLayoutAvailable = FALSE;
        if (!is_null($object)) {
            $validLayouts = Object\Service::getValidLayouts($object);
            if (isset($validLayouts[0])) {
                $masterLayoutAvailable = TRUE;
            }
        }

        foreach ($newsTypes as $typeId => &$type) {

            $customLayoutId = NULL;

            //if string (name) is given, get layout via listing
            if (is_string($type['customLayoutId'])) {
                $list = new ClassDefinition\CustomLayout\Listing();
                $list->setLimit(1);
                $list->setCondition('name = ?', $type['customLayoutId']);
                $list = $list->load();
                if (isset($list[0]) && $list[0] instanceof Object\ClassDefinition\CustomLayout) {
                    $customLayoutId = (int)$list[0]->getId();
                } else {
                    $customLayoutId = 0; //reset layout to default -> custom layout is not available!
                }
            } elseif (is_numeric($type['customLayoutId'])) {
                $customLayoutId = $type['customLayoutId'];
            }

            //remove types if valid layout is set and user is not allowed to use it!
            if (!is_null($customLayoutId)) {
                // custom layout found: check if user has rights to use it! if not: remove from selection!
                if ($validLayouts !== NULL && $masterLayoutAvailable === FALSE && !isset($validLayouts[$customLayoutId])) {
                    unset($newsTypes[$typeId]);
                } else {
                    $type['customLayoutId'] = $customLayoutId;
                }
            } else {
                $type['customLayoutId'] = 0;
            }
        }

        return $newsTypes;
    }

    /**
     * Extract first type element and add first key value as "key" to reference.
     *
     * @return mixed
     */
    public static function getDefaultType()
    {
        $types = static::getTypes();
        $firstKey = current(array_keys($types));
        $firstElement = reset($types);
        $firstElement['key'] = $firstKey;

        return $firstElement;
    }

    /**
     * @param $name
     * @return array|mixed
     */
    public static function getTypeInfo($name)
    {
        $info = [];
        $types = static::getTypes();

        $locale = $_REQUEST['systemLocale'];
        $translator = NULL;

        if (!$locale) {
            if (\Zend_Registry::isRegistered('Zend_Locale')) {
                $locale = \Zend_Registry::get('Zend_Locale');
            } else {
                $locale = new \Zend_Locale('en');
            }
        }

        if ($locale) {
            $translator = new TranslateAdapter($locale);
        }

        if (isset($types[$name])) {
            $info = $types[$name];
            //translate name.
            $info['name'] = !is_null($translator) ? $translator->translate($types[$name]['name']) : $types[$name]['name'];
        }

        return $info;
    }


    /**
     * @return array|mixed|null
     */
    public static function getTypesFromConfig()
    {
        $newsTypes = Configuration::get('entry_types');

        //cannot be empty - at least "news" is required.
        if (empty($newsTypes)) {
            $newsTypes = [
                'news' => [
                    'name'           => 'News',
                    'route'          => '',
                    'customLayoutId' => 0
                ]
            ];
        }

        return $newsTypes;
    }

    /**
     * @param $entryType
     *
     * @return array
     */
    public static function getRouteInfo($entryType)
    {
        //use cache.
        if (isset(static::$routeData[$entryType])) {
            return static::$routeData[$entryType];
        }

        $routeData = ['name' => 'news_detail', 'urlParams' => []];
        $types = static::getTypesFromConfig();

        if (isset($types[$entryType]) && !empty($types[$entryType]['route'])) {
            $routeData['name'] = $types[$entryType]['route'];
        }

        $siteId = NULL;
        if (Site::isSiteRequest()) {
            $siteId = Site::getCurrentSite()->getId();
        }

        $route = Staticroute::getByName($routeData['name'], $siteId);
        $variables = explode(',', $route->getVariables());

        //remove default one
        $defaults = ['news'];
        $variables = array_diff($variables, $defaults);

        $routeData['urlParams'] = array_merge($routeData['urlParams'], $variables);
        static::$routeData[$entryType] = $routeData;

        return $routeData;
    }
}