<?php

namespace NewsBundle\Controller;

use NewsBundle\Generator\LinkGenerator;
use NewsBundle\Model\EntryInterface;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\Object;
use Pimcore\Model\Asset;
use Pimcore\Tool;
use Symfony\Component\HttpFoundation\Request;

class EntryController extends FrontendController
{
    public function detailAction(Request $request)
    {
        $newsFragment = $request->attributes->get('entry');
        $locale = $request->attributes->get('_locale');

        //because this is a virtual document made with static route, we append some document properties with settings, if set.
        $pageProperties = $this->container->get('news.configuration');

        if (!empty($pageProperties)) {
            foreach ($pageProperties as $pagePropertyName => $pagePropertyData) {
                $this->document->setProperty($pagePropertyName, $pagePropertyData['type'], $pagePropertyData['data'], FALSE, FALSE);
            }
        }

        /** @var Object\NewsEntry $solution */
        $entry = Object\NewsEntry::getByLocalizedfields('detailUrl', $newsFragment, $locale, ['limit' => 1]);

        if (!($entry instanceof Object\NewsEntry)) {
            throw new \Exception('Entry (' . $newsFragment . ') couldn\'t be found');
        } else {
            $params = [
                'entry' => $entry
            ];
        }

        //$this->setSEOMeta($entry);

        return $this->renderTemplate('@News/Detail/detail.html.twig', $params);
    }

    /**
     * @param EntryInterface $entry
     */
    private function setSEOMeta(EntryInterface $entry)
    {
        /** @var LinkGenerator $linkGenerator */
        $linkGenerator = $this->container->get('news.generator.link');

        $href = $linkGenerator->generateDetailLink($entry);

        $mT = $entry->getMetaTitle();
        $mD = $entry->getMetaDescription();

        $title = !empty($mT) ? $mT : $entry->getName();
        $description = !empty($mD) ? $mD : ($entry->getLead() ? $entry->getLead() : $entry->getDescription());

        $description = trim(substr($description, 0, 160));

        $host = Tool::getHostUrl();

        $ogTitle = $title;
        $ogDescription = $description;
        $ogUrl = $host . $href;
        $ogType = 'article';

        $ogImage = NULL;

        if ($entry->getImage() instanceof Asset\Image) {
            $ogImage = $host . $entry->getImage()->getThumbnail('contentImage');
        }

        $params = [
            'title'          => $title,
            'description'    => $description,
            'og:title'       => $ogTitle,
            'og:description' => $ogDescription,
            'og:url'         => $ogDescription,
            'og:image'       => $ogImage
        ];

        /* @todo: ?
        $cmdEv = \Pimcore::getEventManager()->trigger('news.head.meta', NULL, $params);

        if ($cmdEv->stopped()) {
            $customMeta = $cmdEv->last();

            if (is_array($customMeta)) {
                $title = $customMeta['title'];
                $description = $customMeta['description'];
                $ogTitle = $customMeta['og:title'];
                $ogDescription = $customMeta['og:description'];
                $ogUrl = $customMeta['og:url'];
                $ogImage = $customMeta['og:image'];
            }
        }

        */

        $this->view->headTitle($title);
        $this->view->headMeta()->setName('description', $description);

        if (!empty($ogTitle)) {
            $this->view->headMeta()->appendName('og:title', $ogTitle);
        }

        if (!empty($ogDescription)) {
            $this->view->headMeta()->appendName('og:description', $ogDescription);
        }

        if (!empty($ogUrl)) {
            $this->view->headMeta()->appendName('og:url', $ogUrl);
        }

        if (!empty($ogType)) {
            $this->view->headMeta()->appendName('og:type', $ogType);
        }

        if (!is_null($ogImage)) {
            $this->view->headMeta()->appendName('og:image', $ogImage);
        }
    }
}
