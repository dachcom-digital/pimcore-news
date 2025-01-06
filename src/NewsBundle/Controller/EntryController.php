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

        $entry = DataObject\NewsEntry::getByLocalizedfields('detailUrl', $newsFragment, $locale, 1);

        if (!$entry instanceof DataObject\NewsEntry) {
            throw new NotFoundHttpException(sprintf('Entry %s not found', $newsFragment));
        }

        return $this->renderTemplate('@News/Detail/detail.html.twig', [
            'entry' => $entry
        ]);
    }
}
