<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

class News extends Document\Tag\Area\AbstractArea {

    public function action() {

        $news = new \Pimcore\Model\Object\NewsEntry();

        $pageRequest = $this->getParam('page');

        $showLatest = $this->view->checkbox('latest')->getData() === '1';
        $showPagination = $this->view->checkbox('showPagination')->getData() === '1';

        $category = $this->view->href('category')->getElement();

        $itemsPerPage = $this->view->numeric('limit')->getData();

        if ( $showPagination && ( empty($itemsPerPage) || $itemsPerPage >= $showPagination ) ) {
            $itemsPerPage = $this->view->numeric('itemsPerPage')->getData();
        }
        else {
            $showPagination = false;
        }

        $page = !empty($pageRequest) ? (int)$pageRequest : 0;

        $sortBy = $this->view->select('sortby')->getData() ?: 'date';
        $orderBy = $this->view->select('orderby')->getData() ?: 'desc';

        $this->view->assign('showPagination', $showPagination);
        $this->view->assign('category', $category);

        $this->view->assign('paginator', $news->getEntriesPaging($category, $page, $itemsPerPage, [
            'field' => $sortBy,
            'dir'   => $orderBy
        ], $showLatest));
    }

}