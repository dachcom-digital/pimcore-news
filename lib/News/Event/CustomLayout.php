<?php

namespace News\Event;

use Pimcore\Model\Object\NewsEntry;
use Pimcore\Model\Object\Service;
use Pimcore\Model\Object\ClassDefinition\CustomLayout as CustomLayoutDefinition;
use News\Tool\NewsTypes;

class CustomLayout
{
    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public static function setNewsTypeLayout(\Zend_EventManager_Event $e)
    {
        /** @var \Pimcore\Model\Object\NewsEntry $object */
        $object = $e->getParam('object');
        $target = $e->getTarget();

        /** @var \Pimcore\Model\Tool\Admin\EventDataContainer $returnValueContainer */
        $returnValueContainer = $e->getParam('returnValueContainer');
        $data = $returnValueContainer->getData();

        //find out which layout has been defined.
        if (!$object instanceof NewsEntry) {
            return FALSE;
        }

        //this param is available if user is reloading the project. do not interfere.
        if (!is_null($target->getParam('layoutId')) && !empty($target->getParam('layoutId'))) {
            return FALSE;
        }

        $layoutId = NULL;
        $layoutType = $object->getEntryType();
        $newsTypes = NewsTypes::getTypes();

        //watch out, a new object is coming in!
        if(is_null($layoutType)) {
            $defaultType = NewsTypes::getDefaultType();
            $layoutType = $defaultType['key'];
        }

        foreach ($newsTypes as $typeName => $type) {
            if (!isset($type['customLayoutId']) || !is_numeric($type['customLayoutId'])) {
                continue;
            }

            if ($layoutType === $typeName) {
                $layoutId = $type['customLayoutId'];
                break;
            }
        }

        if (!is_null($layoutId)) {

            $customLayout = NULL;

            try {
                $customLayout = CustomLayoutDefinition::getById($layoutId);
            } catch (\Exception$e) {
                //not found. fail silently.
            }

            if ($customLayout instanceof CustomLayoutDefinition) {
                $customLayoutDefinition = $customLayout->getLayoutDefinitions();
                Service::enrichLayoutDefinition($customLayoutDefinition, $object);
                $data['layout'] = $customLayoutDefinition;
                $data['currentLayoutId'] = $layoutId;

            }
        }

        $returnValueContainer->setData($data);
    }
}