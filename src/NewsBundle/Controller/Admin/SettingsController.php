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

namespace NewsBundle\Controller\Admin;

use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsController extends AdminAbstractController
{
    protected TranslatorInterface $adminTranslator;
    protected EntryTypeManager $entryTypeManager;

    public function __construct(
        TranslatorInterface $adminTranslator,
        EntryTypeManager $entryTypeManager
    ) {
        $this->adminTranslator = $adminTranslator;
        $this->entryTypeManager = $entryTypeManager;
    }

    public function getEntryTypesAction(Request $request): JsonResponse
    {
        $newsObject = DataObject::getById((int) $request->get('objectId'));

        $valueArray = [];
        foreach ($this->entryTypeManager->getTypes($newsObject) as $typeName => $type) {
            $valueArray[] = [
                'custom_layout_id' => $type['custom_layout_id'],
                'value'            => $typeName,
                'key'              => $this->adminTranslator->trans($type['name'], [], 'admin'),
                'default'          => $this->entryTypeManager->getDefaultType()
            ];
        }

        return $this->json([
            'options' => $valueArray,
            'success' => true,
            'message' => ''
        ]);
    }

    public function changeEntryTypeAction(Request $request): JsonResponse
    {
        $entryTypeId = $request->get('entryTypeId');
        $object = DataObject::getById((int) $request->get('objectId'));

        if ($object instanceof DataObject\NewsEntry) {
            $object->setEntryType($entryTypeId);
            Version::disable();
            $object->setOmitMandatoryCheck(true);
            $object->save();
            Version::enable();
        }

        return $this->json([
            'entryTypeId' => $entryTypeId,
            'success'     => true,
            'message'     => ''
        ]);
    }
}
