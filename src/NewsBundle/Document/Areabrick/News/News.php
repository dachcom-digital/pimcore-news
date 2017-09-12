<?php

namespace NewsBundle\Document\Areabrick\News;

use NewsBundle\Configuration\Configuration;
use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;
use Pimcore\Model\DataObject;
use Pimcore\Translation\Translator;
use Pimcore\Model\Document;

class News extends AbstractTemplateAreabrick
{
    protected $document;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var EntryTypeManager
     */
    protected $entryTypeManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Form constructor.
     *
     * @param Configuration    $configuration
     * @param EntryTypeManager $entryTypeManager
     * @param Translator       $translator
     */
    public function __construct(
        Configuration $configuration,
        EntryTypeManager $entryTypeManager,
        Translator $translator
    ) {
        $this->configuration = $configuration;
        $this->entryTypeManager = $entryTypeManager;
        $this->configuration = $configuration;
        $this->translator = $translator;
    }

    /**
     * @param Document\Tag\Area\Info $info
     */
    public function action(Info $info)
    {
        $this->document = $info->getDocument();

        $view = $info->getView();

        $querySettings = [];

        $fieldConfiguration = $this->setDefaultFieldValues();

        $querySettings['category'] = $fieldConfiguration['category']['value'];
        $querySettings['includeSubCategories'] = $fieldConfiguration['include_subcategories']['value'];
        $querySettings['entryType'] = $fieldConfiguration['entry_types']['value'];

        //set limit
        $limit = $fieldConfiguration['max_items']['value'];

        //set pagination
        $calculatedItemsPerPage = $fieldConfiguration['paginate']['items_per_page']['value'];

        if ($calculatedItemsPerPage > $limit) {
            $calculatedItemsPerPage = $limit;
        }

        $querySettings['itemsPerPage'] = $calculatedItemsPerPage;

        //set paged
        $querySettings['page'] = (int)$info->getRequest()->query->get('page');

        //only latest
        if ($fieldConfiguration['latest']['value'] === TRUE) {
            $querySettings['where']['latest = ?'] = 1;
        }

        //set sort
        $querySettings['sort']['field'] = $fieldConfiguration['sort_by']['value'];
        $querySettings['sort']['dir'] = $fieldConfiguration['order_by']['value'];

        //set time range
        $querySettings['timeRange'] = $fieldConfiguration['time_range']['value'];

        //get request data
        $querySettings['request'] = [
            'POST' => $info->getRequest()->request->all(),
            'GET'  => $info->getRequest()->query->all()
        ];

        //load Query
        $newsObjects = DataObject\NewsEntry::getEntriesPaging($querySettings);

        $mainClasses = [];
        $mainClasses[] = 'area';
        $mainClasses[] = 'news-' . $fieldConfiguration['layouts']['value'];

        if ($fieldConfiguration['entry_types']['value'] !== 'all') {
            $mainClasses[] = 'entry-type-' . str_replace(['_', ' '], ['-'], strtolower($fieldConfiguration['entry_types']['value']));
        }

        //prepare WidgetSettings
        //@todo: implement widget handler

        //$widgetSettings = $querySettings;
        //$widgetSettings['showPagination'] = $fieldConfiguration['show_pagination']['value'];
        //$widgetSettings['entryType'] = $fieldConfiguration['entry_types']['value'];
        //$widgetSettings['layoutName'] = $fieldConfiguration['layouts']['value'];
        //$widgetSettings['paginator'] = $newsObjects;

        //$widgetHandler = new WidgetHandler($widgetSettings);
        //$widgetHandler->passHelperPaths($view->getHelperPaths());
        //$widgetHandler->setEditMode($view->editmode);

        $params = [
            //'widgetHandler'  => $widgetHandler,

            'main_classes'    => implode(' ', $mainClasses),
            'category'        => $fieldConfiguration['category']['value'],
            'show_pagination' => $fieldConfiguration['show_pagination']['value'],
            'entry_type'      => $fieldConfiguration['entry_types']['value'],
            'layout_name'     => $fieldConfiguration['layouts']['value'],
            'paginator'       => $newsObjects,

            //system/editmode related
            'config'          => $fieldConfiguration,
            'query_settings'  => $querySettings

        ];

        foreach ($params as $key => $value) {
            $view->{$key} = $value;
        }
    }

    /**
     * Set Configuration and set defaults to view fields if they're empty.
     */
    private function setDefaultFieldValues()
    {
        $adminSettings = [];

        $listConfig = $this->configuration->getConfig('list');

        //set latest
        $adminSettings['latest'] = ['value' => (bool)$this->getDocumentField('checkbox', 'latest')->getData()];

        //category
        $hrefConfig = ['types' => ['object'], 'subtypes' => ['object' => ['object']], 'classes' => ['NewsCategory'], 'width' => '95%'];
        $adminSettings['category'] = ['value' => NULL, 'href_config' => $hrefConfig];
        $categoryElement = $this->getDocumentField('href', 'category');
        if (!$categoryElement->isEmpty()) {
            $adminSettings['category']['value'] = $categoryElement->getElement();
        }

        //subcategories
        $adminSettings['include_subcategories'] = ['value' => (bool)$this->getDocumentField('checkbox', 'includeSubCategories')->getData()];

        //show pagination
        $adminSettings['show_pagination'] = ['value' => (bool)$this->getDocumentField('checkbox', 'showPagination')->getData()];

        //set layout
        $adminSettings['layouts'] = ['store' => $this->getLayoutStore(), 'value' => $listConfig['layouts']['default']];
        $layoutElement = $this->getDocumentField('select', 'layout');
        if ($layoutElement->isEmpty()) {
            $layoutElement->setDataFromResource($adminSettings['layouts']['value']);
        } else {
            $adminSettings['layouts']['value'] = $layoutElement->getData();
        }

        //set items per page
        $adminSettings['paginate'] = ['items_per_page' => ['value' => $listConfig['paginate']['items_per_page']]];
        $itemsPerPageElement = $this->getDocumentField('numeric', 'itemsPerPage');
        if ($itemsPerPageElement->isEmpty()) {
            $itemsPerPageElement->setDataFromResource($adminSettings['paginate']['items_per_page']['value']);
        } else {
            $adminSettings['paginate']['items_per_page']['value'] = (int)$itemsPerPageElement->getData();
        }

        //set limit
        $adminSettings['max_items'] = ['value' => $listConfig['max_items']];
        $limitElement = $this->getDocumentField('numeric', 'limit');
        if ($limitElement->isEmpty() || $itemsPerPageElement->getData() < 0) {
            $limitElement->setDataFromResource($adminSettings['max_items']['value']);
        } else {
            $adminSettings['max_items']['value'] = (int)$limitElement->getData();
        }

        //set sort by
        $adminSettings['sort_by'] = ['store' => $this->getSortByStore(), 'value' => $listConfig['sort_by']];
        $sortByElement = $this->getDocumentField('select', 'sortby');
        if ($sortByElement->isEmpty()) {
            $sortByElement->setDataFromResource($adminSettings['sort_by']['value']);
        } else {
            $adminSettings['sort_by']['value'] = $sortByElement->getData();
        }

        //set order by
        $adminSettings['order_by'] = ['store' => $this->getOrderByStore(), 'value' => $listConfig['order_by']];
        $orderByElement = $this->getDocumentField('select', 'orderby');
        if ($orderByElement->isEmpty()) {
            $orderByElement->setDataFromResource($adminSettings['order_by']['value']);
        } else {
            $adminSettings['order_by']['value'] = $orderByElement->getData();
        }

        //set time range
        $adminSettings['time_range'] = ['store' => $this->getTimeRangeStore(), 'value' => $listConfig['time_range']];
        $timeRangeElement = $this->getDocumentField('select', 'timeRange');
        if ($timeRangeElement->isEmpty()) {
            $timeRangeElement->setDataFromResource($adminSettings['time_range']['value']);
        } else {
            $adminSettings['time_range']['value'] = $timeRangeElement->getData();
        }

        //set entry type
        $adminSettings['entry_types'] = ['store' => $this->getEntryTypeStore(), 'value' => 'all'];
        $entryTypeElement = $this->getDocumentField('select', 'entryType');
        if ($entryTypeElement->isEmpty()) {
            $entryTypeElement->setDataFromResource($adminSettings['entry_types']['value']);
        } else {
            $adminSettings['entry_types']['value'] = $entryTypeElement->getData();
        }

        return $adminSettings;
    }

    private function getLayoutStore()
    {
        $listConfig = $this->configuration->getConfig('list');

        $store = [];
        foreach ($listConfig['layouts']['items'] as $index => $item) {
            $store[] = [$index, $this->translator->trans($item['name'], [], 'admin')];
        }

        return $store;
    }

    private function getSortByStore()
    {
        return [
            ['date', $this->translator->trans('news.sort_by.date', [], 'admin')],
            ['name', $this->translator->trans('news.sort_by.name', [], 'admin')]
        ];
    }

    private function getOrderByStore()
    {
        return [
            ['date', $this->translator->trans('news.order_by.descending', [], 'admin')],
            ['name', $this->translator->trans('news.order_by.ascending', [], 'admin')]
        ];
    }

    private function getTimeRangeStore()
    {
        return [
            ['all', $this->translator->trans('news.time_range.all_entries', [], 'admin')],
            ['current', $this->translator->trans('news.time_range.current_entries', [], 'admin')],
            ['past', $this->translator->trans('news.time_range.past_entries', [], 'admin')]
        ];
    }

    private function getEntryTypeStore()
    {
        $store = [
            ['all', $this->translator->trans('news.entry_type.all', [], 'admin')]
        ];

        foreach ($this->entryTypeManager->getTypesFromConfig() as $typeKey => $typeData) {
            $store[] = [$typeKey, $this->translator->trans($typeData['name'], [], 'admin')];
        }

        return $store;
    }

    private function getDocumentField($type, $inputName)
    {
        return $this->getDocumentTag($this->document, $type, $inputName);
    }

    /**
     * @return bool
     */
    public function hasEditTemplate()
    {
        return TRUE;
    }

    /**
     * @return string
     */
    public function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'News';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagOpen(Info $info)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagClose(Info $info)
    {
        return '';
    }
}