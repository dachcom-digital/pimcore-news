<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;
use Pimcore\Model\Object;

class News extends Document\Tag\Area\AbstractArea
{
    /**
     *
     */
    public function action()
    {
        $pageRequest = $this->getParam('page');

        $querySettings = [];

        if ($this->view->href('category')->getElement()) {

            $querySettings['category'] = $this->view->href('category')->getElement();

            $this->view->assign('category', $this->view->href('category')->getElement());

            if ($this->view->checkbox('includeSubCategories')->getData() === '1') {

                $querySettings['includeSubCategories'] = TRUE;
            }
        }

        $limit = (int)$this->view->numeric('limit')->getData();

        if ($this->view->checkbox('showPagination')->getData() === '1') {

            $this->view->assign('showPagination', TRUE);

            $itemsPerPage = (int)$this->view->numeric('itemsPerPage')->getData();

            if ((empty($limit) || $itemsPerPage > $limit)) {

                $querySettings['itemsPerPage'] = $itemsPerPage;
            } else if (!empty($limit)) {

                $querySettings['itemsPerPage'] = $limit;
            }
        }
        else if (!empty($limit)) {
            $querySettings['itemsPerPage'] = $limit;
        }

        $querySettings['page'] = (int)$pageRequest;

        if ($this->view->checkbox('latest')->getData() === '1') {
            $querySettings['where']['latest = ?'] = 1;
        }

        $querySettings['sort']['field'] = $this->view->select('sortby')->getData() ?: 'date';
        $querySettings['sort']['dir'] = $this->view->select('orderby')->getData() ?: 'desc';

        $newsObjects = Object\NewsEntry::getEntriesPaging($querySettings);

        $this->view->assign('paginator', $newsObjects);
    }

}