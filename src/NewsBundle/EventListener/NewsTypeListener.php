<?php

namespace NewsBundle\EventListener;

use NewsBundle\Configuration\Configuration;
use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Event\AdminEvents;
use Pimcore\Model\Object\ClassDefinition\CustomLayout;
use Pimcore\Model\Object\NewsEntry;
use Pimcore\Model\Object\Service;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class NewsTypeListener implements EventSubscriberInterface
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
     * @param RequestStack    $requestStack
     * @param Configuration    $configuration
     * @param EntryTypeManager $entryTypeManager
     */
    public function __construct(RequestStack $requestStack, Configuration $configuration, EntryTypeManager $entryTypeManager)
    {
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
            AdminEvents::OBJECT_GET_PRE_SEND_DATA => 'setNewsTypeLayout'
        ];
    }

    /**
     * @param GenericEvent $e
     */
    public function setNewsTypeLayout(GenericEvent $e)
    {
        /** @var \Pimcore\Model\Object\NewsEntry $object */
        $object = $e->getArgument('object');
        $data = $e->getArgument('data');

        if (!$object instanceof NewsEntry) {
            return;
        }

        $layoutId = NULL;
        $layoutType = $object->getEntryType();

        $entryTypes = $this->entryTypeManager->getTypes();

        //watch out, a new object is coming in!
        if (is_null($layoutType)) {
            $layoutType = $this->entryTypeManager->getDefaultType();
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

        if (!is_null($layoutId)) {

            $customLayout = NULL;

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
            }
        }

        $e->setArgument('data', $data);
    }
}