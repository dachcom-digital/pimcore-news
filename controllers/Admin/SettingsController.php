<?php

use Pimcore\Controller\Action\Admin;
use News\Tool\NewsTypes;

class News_Admin_SettingsController extends Admin
{
    /**
     *
     */
    public function getNewsTypesAction()
    {
        $newsObject = Object::getById(intval($this->getParam('objectId')));
        $newsTypes = NewsTypes::getTypes($newsObject);

        $valueArray = [];

        foreach ($newsTypes as $typeName => $type) {

            $valueArray[] = [
                'customLayoutId' => $type['customLayoutId'],
                'value'          => $typeName,
                'key'            => $this->view->translateAdmin($type['name'])
            ];
        }

        $this->_helper->json(
            [
                'options' => $valueArray,
                'success' => TRUE,
                'message' => ''
            ]
        );
    }
}