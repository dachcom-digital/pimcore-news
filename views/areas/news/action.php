<?php

namespace Pimcore\Model\Document\Tag\Area;

use News\Model\Configuration;
use News\Tool\NewsTypes;
use Pimcore\Model\Document;
use Pimcore\Model\Object;

class News extends Document\Tag\Area\AbstractArea
{
    /**
     *
     */
    public function action()
    {
        $view = $this->getView();
        $querySettings = [];

        //set category
        $category = NULL;
        if ($view->href('category')->getElement()) {

            $querySettings['category'] = $view->href('category')->getElement();
            $category = $view->href('category')->getElement();

            if ($view->checkbox('includeSubCategories')->getData() === '1') {
                $querySettings['includeSubCategories'] = TRUE;
            }
        }

        //set entry type
        $entryType = $view->select('entryType')->getData() ?: 'all';
        $querySettings['entryType'] = $entryType;

        //set limit
        $limit = (int)$view->numeric('limit')->getData();

        //set pagination
        $showPagination = FALSE;
        if ($view->checkbox('showPagination')->getData() === '1') {

            $showPagination = TRUE;
            $itemsPerPage = (int)$view->numeric('itemsPerPage')->getData();

            if (empty($limit) || $itemsPerPage > $limit) {
                $querySettings['itemsPerPage'] = $itemsPerPage;
            } else if (!empty($limit)) {
                $querySettings['itemsPerPage'] = $limit;
            }
        } else if (!empty($limit)) {
            $querySettings['itemsPerPage'] = $limit;
        }

        //set paged
        $querySettings['page'] = (int)$this->getParam('page');

        //only latest
        if ($view->checkbox('latest')->getData() === '1') {
            $querySettings['where']['latest = ?'] = 1;
        }

        //set sort
        $querySettings['sort']['field'] = $view->select('sortby')->getData() ?: 'date';
        $querySettings['sort']['dir'] = $view->select('orderby')->getData() ?: 'desc';

        //load Query
        $newsObjects = Object\NewsEntry::getEntriesPaging($querySettings);

        //load settings for edit.php in edit-mode
        $adminSettings = [];
        if ($view->editmode === TRUE) {

            $adminSettings['listSettings'] = Configuration::get('news_list_settings');
            foreach ($adminSettings['listSettings']['layouts']['items'] as $index => $item) {
                $adminSettings['listSettings']['layouts']['items'][$index] = [$item[0], $view->translateAdmin($item[1])];
            }

            $newsTypes = NewsTypes::getTypesFromConfig();
            $adminSettings['entryTypes']['store'] = [['all', $view->translateAdmin('all entry types')]];
            $adminSettings['entryTypes']['default'] = 'all';
            foreach ($newsTypes as $typeKey => $typeData) {
                $adminSettings['entryTypes']['store'][] = [$typeKey, $view->translateAdmin($typeData['name'])];
            }
        }

        $mainClasses = [];

        $mainClasses[] = 'area';
        $mainClasses[] = 'news-' . $view->select('layout')->getData();

        if($entryType !== 'all') {
            $mainClasses[] = 'entry-type-' . str_replace(['_',' '],['-'], strtolower($entryType));
        }

        $view->assign([
            'mainClasses'    => implode(' ', $mainClasses),
            'category'       => $category,
            'showPagination' => $showPagination,
            'paginator'      => $newsObjects,
            'entryType'      => $entryType,
            'editSettings'   => $adminSettings
        ]);
    }

    public function getBrickHtmlTagOpen($brick)
    {
        return '';
    }

    public function getBrickHtmlTagClose($brick)
    {
        return '';
    }
}