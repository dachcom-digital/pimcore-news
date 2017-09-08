<?php

namespace NewsBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;

class EntryController extends FrontendController
{
    public function detailAction(Request $request)
    {
        $newsFragment = $request->attributes->get('entry');
        $locale = $request->attributes->get('_locale');

        /** @var DataObject\NewsEntry $entry */
        $entry = DataObject\NewsEntry::getByLocalizedfields('detailUrl', $newsFragment, $locale, ['limit' => 1]);

        if (!($entry instanceof DataObject\NewsEntry)) {
            throw new \Exception('Entry (' . $newsFragment . ') couldn\'t be found');
        } else {
            $params = [
                'entry' => $entry
            ];
        }

        return $this->renderTemplate('@News/Detail/detail.html.twig', $params);
    }
}
