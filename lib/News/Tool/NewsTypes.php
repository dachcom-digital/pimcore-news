<?php

namespace News\Tool;

use Pimcore\Model\Object;
use Pimcore\Model\Object\ClassDefinition;
use News\Model\Configuration;

class NewsTypes
{
    /**
     * @param null $object
     *
     * @return array|mixed|null
     */
    public static function getTypes($object = NULL)
    {
        $newsTypes = static::getTypesFromConfig();

        $validLayouts = NULL;

        if (!is_null($object)) {
            $validLayouts = Object\Service::getValidLayouts($object);
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
                    $customLayoutId = NULL; //reset field -> custom layout is not available!
                }
            }

            //remove types if user is not allowed to use it!
            $allowMasterLayout = isset($validLayouts[0]);

            if ((!$allowMasterLayout || !is_null($customLayoutId)) && !is_null($validLayouts) && !isset($validLayouts[$customLayoutId])) {
                unset($newsTypes[ $typeId]);
            } else {
                $type['customLayoutId'] = $customLayoutId;
            }
        }

        return $newsTypes;
    }

    /**
     * Extract first type element and add first key value as "key" to reference.
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
                    'customLayoutId' => NULL
                ]
            ];
        }

        return $newsTypes;
    }

    /**
     * @param $entryType
     *
     * @return string
     */
    public static function getRouteName($entryType)
    {
        $defaultStaticRoute = 'news_detail';
        $types = static::getTypesFromConfig();

        if(isset($types[$entryType]) && !empty($types[$entryType]['route'])) {
            return $types[$entryType]['route'];
        }

        return $defaultStaticRoute;
    }
}