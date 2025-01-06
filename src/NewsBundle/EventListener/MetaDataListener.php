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

use NewsBundle\Generator\HeadMetaGeneratorInterface;
use NewsBundle\Model\EntryInterface;
use Pimcore\Model\DataObject\NewsEntry;
use Pimcore\Twig\Extension\Templating\HeadMeta;
use Pimcore\Twig\Extension\Templating\HeadTitle;
use Pimcore\Twig\Extension\Templating\Placeholder\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MetaDataListener implements EventSubscriberInterface
{
    protected HeadMeta $headMeta;
    protected HeadTitle $headTitle;
    protected HeadMetaGeneratorInterface $headMetaGenerator;

    public function __construct(
        HeadMeta $headMeta,
        HeadTitle $headTitle,
        HeadMetaGeneratorInterface $headMetaGenerator
    ) {
        $this->headMeta = $headMeta;
        $this->headTitle = $headTitle;
        $this->headMetaGenerator = $headMetaGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        if ($request->attributes->get('pimcore_request_source') !== 'staticroute') {
            return;
        }

        $entryId = $request->get('entry');

        if (empty($entryId)) {
            return;
        }

        /** @var EntryInterface $entry */
        $entry = NewsEntry::getByLocalizedfields('detailUrl', $entryId, $request->getLocale(), ['limit' => 1]);

        if (!$entry instanceof NewsEntry) {
            return;
        }

        foreach ($this->headMetaGenerator->generateMeta($entry) as $property => $content) {
            if (!empty($content)) {
                $this->headMeta->appendProperty($property, $content);
            }
        }

        $this->headMeta->setDescription($this->headMetaGenerator->generateDescription($entry));

        $title = $this->headMetaGenerator->generateTitle($entry);

        switch ($this->headMetaGenerator->getTitlePosition()) {
            case Container::SET:
                $this->headTitle->set($title);

                break;
            case Container::PREPEND:
                $this->headTitle->prepend($title);

                break;
            case Container::APPEND:
            default:
                $this->headTitle->append($title);

                break;
        }
    }
}
