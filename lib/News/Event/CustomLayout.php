<?php

namespace News\Event;

use Pimcore\Tool\Admin as AdminTool;
use Pimcore\Model\Object\NewsEntry;
use Pimcore\Model\Object\Service;
use Pimcore\Model\Object\ClassDefinition\CustomLayout as CustomLayoutDefinition;
use News\Tool\NewsTypes;

class CustomLayout
{
    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return void
     */
    public static function setNewsTypeLayout(\Zend_EventManager_Event $e)
    {
        /** @var \Pimcore\Model\Object\NewsEntry $object */
        $object = $e->getParam('object');
        $target = $e->getTarget();
        $requestedLayoutId = $target->getParam('layoutId');

        /** @var \Pimcore\Model\Tool\Admin\EventDataContainer $returnValueContainer */
        $returnValueContainer = $e->getParam('returnValueContainer');
        $data = $returnValueContainer->getData();

        //find out which layout has been defined.
        if (!$object instanceof NewsEntry) {
            return;
        }

        $layoutId = 0;
        $layoutType = $object->getEntryType();
        $newsTypes = NewsTypes::getTypes($object);

        //remove layouts from pimcore layout selector.
        $data['validLayouts'] = [];

        //this param is available if user is reloading the object. do not interfere.
        if (!is_null($requestedLayoutId) &&
            !empty($requestedLayoutId) &&
            $requestedLayoutId !== '0'
        ) {
            $returnValueContainer->setData($data);
            return;
        }

        //request of default layout definition
        if ($requestedLayoutId === '0') {
            $data['currentLayoutId'] = 0;
            $data['layout'] = $object->getClass()->getLayoutDefinitions();
            $returnValueContainer->setData($data);
            return;
        }

        //watch out, a new object is coming in!
        if (is_null($layoutType)) {
            $defaultType = NewsTypes::getDefaultType();
            $layoutType = $defaultType['key'];
            //check if default type exists for current user. if not: use the first available type!
            if (!isset($newsTypes[$layoutType])) {
                $layoutType = array_keys($newsTypes)[0];
            }
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

        //check if user is allowed to open this object.
        if (!isset($newsTypes[$layoutType])) {
            $user = AdminTool::getCurrentUser();
            if (!$user->isAdmin()) {
                $data['_invalidNewsType'] = TRUE;
                $data['layout'] = NULL;
                $data['currentLayoutId'] = NULL;
                $returnValueContainer->setData($data);
                return;
            }
        }

        if ($layoutId !== 0) {
            $customLayout = NULL;
            try {
                $customLayout = CustomLayoutDefinition::getById($layoutId);
            } catch (\Exception $e) {
                //not found. fail silently.
            }

            if ($customLayout instanceof CustomLayoutDefinition) {
                $customLayoutDefinition = $customLayout->getLayoutDefinitions();
                Service::enrichLayoutDefinition($customLayoutDefinition, $object);
                $data['layout'] = $customLayoutDefinition;
                $data['currentLayoutId'] = $layoutId;
            } else {
                $data['currentLayoutId'] = 0;
                $data['layout'] = $object->getClass()->getLayoutDefinitions();
            }
        } else {
            $data['currentLayoutId'] = 0;
            $data['layout'] = $object->getClass()->getLayoutDefinitions();
        }

        $returnValueContainer->setData($data);
    }
}