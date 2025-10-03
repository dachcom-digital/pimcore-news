<?php

namespace NewsBundle\EventListener;

use NewsBundle\Model\AdminStyle;
use NewsBundle\Model\Entry;
use Pimcore\Bundle\AdminBundle\Event\AdminEvents;
use Pimcore\Bundle\AdminBundle\Event\ElementAdminStyleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Registry;

class AdminStyleListener implements EventSubscriberInterface
{
    public function __construct(protected Registry $workflowRegistry)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminEvents::RESOLVE_ELEMENT_ADMIN_STYLE => 'addAdminStyle'
        ];
    }

    public function addAdminStyle(ElementAdminStyleEvent $event): void
    {
        if ($event->getElement() instanceof Entry) {
            $event->setAdminStyle(new AdminStyle($event->getElement()));
        }
    }
}