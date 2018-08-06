<?php

namespace NewsBundle\Controller\Admin;

use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends AdminController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getEntryTypesAction(Request $request)
    {
        /** @var EntryTypeManager $configuration */
        $entryTypeManager = $this->get(EntryTypeManager::class);

        /** @var Translator $translator */
        $translator = $this->get('pimcore.translator');

        $newsObject = DataObject::getById(intval($request->get('objectId')));

        $valueArray = [];
        foreach ($entryTypeManager->getTypes($newsObject) as $typeName => $type) {
            $valueArray[] = [
                'custom_layout_id' => $type['custom_layout_id'],
                'value'            => $typeName,
                'key'              => $translator->trans($type['name'], [], 'admin'),
                'default'          => $entryTypeManager->getDefaultType()
            ];
        }

        return $this->json([
            'options' => $valueArray,
            'success' => true,
            'message' => ''
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function changeEntryTypeAction(Request $request)
    {
        $entryTypeId = $request->get('entryTypeId');
        $object = DataObject::getById(intval($request->get('objectId')));

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
