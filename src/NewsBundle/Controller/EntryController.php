<?php

namespace NewsBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\Object;
use Symfony\Component\HttpFoundation\Request;

class EntryController extends FrontendController
{
    public function detailAction(Request $request)
    {
        $newsFragment = $request->attributes->get('entry');
        $locale = $request->attributes->get('_locale');

        /** @var Object\NewsEntry $entry */
        $entry = Object\NewsEntry::getByLocalizedfields('detailUrl', $newsFragment, $locale, ['limit' => 1]);

        if (!($entry instanceof Object\NewsEntry)) {
            throw new \Exception('Entry (' . $newsFragment . ') couldn\'t be found');
        } else {
            $params = [
                'entry' => $entry
            ];
        }

        return $this->renderTemplate('@News/Detail/detail.html.twig', $params);
    }
}
