<?php

namespace NewsBundle\EventListener;

use NewsBundle\Configuration\Configuration;
use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Tool\Admin as AdminTool;
use Pimcore\Event\AdminEvents;
use Pimcore\Model\DataObject\ClassDefinition\CustomLayout;
use Pimcore\Model\DataObject\NewsEntry;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class EntryTypeListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var EntryTypeManager
     */
    protected $entryTypeManager;

    /**
     * RestrictionServiceListener constructor.
     *
     * @param RequestStack     $requestStack
     * @param Configuration    $configuration
     * @param EntryTypeManager $entryTypeManager
     */
    public function __construct(
        RequestStack $requestStack,
        Configuration $configuration,
        EntryTypeManager $entryTypeManager
    ) {
        $this->requestStack = $requestStack;
        $this->configuration = $configuration;
        $this->entryTypeManager = $entryTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AdminEvents::OBJECT_GET_PRE_SEND_DATA => 'setEntryTypeLayout'
        ];
    }

    /**
     * @param GenericEvent $e
     */
    public function setEntryTypeLayout(GenericEvent $e)
    {
        /** @var \Pimcore\Model\DataObject\NewsEntry $object */
        $object = $e->getArgument('object');
        $data = $e->getArgument('data');
        $requestedLayoutId = $this->requestStack->getCurrentRequest()->get('layoutId');

        if (!$object instanceof NewsEntry) {
            return;
        }

        //remove layouts from pimcore layout selector.
        $data['validLayouts'] = [];

        //this param is available if user is reloading the object. do not interfere.
        if (!is_null($requestedLayoutId) &&
            !empty($requestedLayoutId) &&
            $requestedLayoutId !== '0'
        ) {
            $e->setArgument('data', $data);
            return;
        }

        $layoutId = 0;
        $layoutType = $object->getEntryType();
        $entryTypes = $this->entryTypeManager->getTypes($object);

        //define default type
        $defaultLayoutType = $this->entryTypeManager->getDefaultType();

        //check if default type exists for current user. if not: use the first available type!
        if ((is_null($layoutType) && !isset($entryTypes[$defaultLayoutType])) && !isset($entryTypes[$layoutType])) {
            $defaultLayoutType = array_keys($entryTypes)[0];
        }

        //request of default layout definition
        if ($requestedLayoutId === '0') {
            $data['currentLayoutId'] = 0;
            $data['layout'] = $object->getClass()->getLayoutDefinitions();
            $e->setArgument('data', $data);
            return;
        }

        //watch out, a new object is coming in!
        if (is_null($layoutType)) {
            $layoutType = $defaultLayoutType;

        }

        foreach ($entryTypes as $typeName => $type) {
            if (!isset($type['custom_layout_id']) || !is_numeric($type['custom_layout_id'])) {
                continue;
            }

            if ($layoutType === $typeName) {
                $layoutId = $type['custom_layout_id'];
                break;
            }
        }

        //check if user is allowed to open this object.
        if (!isset($entryTypes[$layoutType])) {
            $user = AdminTool::getCurrentUser();
            if (!$user->isAdmin()) {
                $data['_invalidEntryType'] = true;
                $data['layout'] = null;
                $data['currentLayoutId'] = null;
                $e->setArgument('data', $data);
                return;
            }
        }

        if ($layoutId !== 0) {
            $customLayout = null;
            try {
                $customLayout = CustomLayout::getById($layoutId);
            } catch (\Exception $e) {
                //not found. fail silently.
            }

            if ($customLayout instanceof CustomLayout) {
                $customLayoutDefinition = $customLayout->getLayoutDefinitions();
                Service::enrichLayoutDefinition($customLayoutDefinition, $object);
                $data['layout'] = $customLayoutDefinition;
                $data['currentLayoutId'] = $layoutId;
            } else {
                $data['layout'] = $object->getClass()->getLayoutDefinitions();
                $data['currentLayoutId'] = 0;
            }
        } else {
            $data['layout'] = $object->getClass()->getLayoutDefinitions();
            $data['currentLayoutId'] = 0;
        }

        $e->setArgument('data', $data);
    }
}