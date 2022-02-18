<?php

namespace NewsBundle\Controller\Admin;

use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends AdminController
{
    protected Translator $translator;
    protected EntryTypeManager $entryTypeManager;

    public function __construct(Translator $translator, EntryTypeManager $entryTypeManager)
    {
        $this->translator = $translator;
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
                'key'              => $this->translator->trans($type['name'], [], 'admin'),
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
