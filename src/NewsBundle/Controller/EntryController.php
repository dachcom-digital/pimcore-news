<?php

namespace NewsBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntryController extends FrontendController
{
    public function detailAction(Request $request): Response
    {
        $newsFragment = $request->attributes->get('entry');
        $locale = $request->attributes->get('_locale');

        $entry = DataObject\NewsEntry::getByLocalizedfields(field: 'detailUrl', value: $newsFragment, locale: $locale, limit: 1);

        if (!$entry instanceof DataObject\NewsEntry) {
            throw new NotFoundHttpException(sprintf('Entry %s not found', $newsFragment));
        }

        return $this->renderTemplate('@News/Detail/detail.html.twig', [
            'entry' => $entry
        ]);
    }
}
