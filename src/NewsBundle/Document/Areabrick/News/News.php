<?php

namespace NewsBundle\Document\Areabrick\News;

use NewsBundle\Registry\PresetRegistry;
use Symfony\Component\Translation\TranslatorInterface;
use NewsBundle\Configuration\Configuration;
use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class News extends AbstractTemplateAreabrick
{
    /**
     * @var Document
     */
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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var PresetRegistry
     */
    protected $presetRegistry;

    /**
     * Form constructor.
     *
     * @param Configuration       $configuration
     * @param EntryTypeManager    $entryTypeManager
     * @param TranslatorInterface $translator
     * @param PresetRegistry      $presetRegistry
     */
    public function __construct(
        Configuration $configuration,
        EntryTypeManager $entryTypeManager,
        TranslatorInterface $translator,
        PresetRegistry $presetRegistry
    ) {
        $this->configuration = $configuration;
        $this->entryTypeManager = $entryTypeManager;
        $this->configuration = $configuration;
        $this->translator = $translator;
        $this->presetRegistry = $presetRegistry;
    }

    /**
     * @param Info $info
     * @return null|\Symfony\Component\HttpFoundation\Response|void
     * @throws \Exception
     */
    public function action(Info $info)
    {
        $this->document = $info->getDocument();

        $view = $info->getView();
        $fieldConfiguration = $this->setDefaultFieldValues();

        $isPresetMode = false;
        $subParams = [];

        //check if preset has been selected at first
        if ($this->isPresetMode($fieldConfiguration)) {
            $isPresetMode = true;
            $preset = $this->presetRegistry->get($fieldConfiguration['presets']['value']);
            $preset->setInfo($info);
            $subParams['preset_name'] = $fieldConfiguration['presets']['value'];
            $subParams['preset'] = [];
            foreach ($preset->getViewParams() as $key => $param) {
                $subParams['preset'][$key] = $param;
            }
        } else {

            $querySettings = [];
            $querySettings['category'] = $fieldConfiguration['category']['value'];
            $querySettings['includeSubCategories'] = $fieldConfiguration['include_subcategories']['value'];
            $querySettings['singleObjects'] = $fieldConfiguration['single_objects']['value'];
            $querySettings['entryType'] = $fieldConfiguration['entry_types']['value'];
            $querySettings['offset'] = $fieldConfiguration['offset']['value'];

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
            if ($fieldConfiguration['latest']['value'] === true) {
                $querySettings['onlyLatest'] = true;
            }

            //set sort
            $querySettings['sort']['field'] = $fieldConfiguration['sort_by']['value'];
            $querySettings['sort']['dir'] = $fieldConfiguration['order_by']['value'];

            //set time range
            $querySettings['timeRange'] = $fieldConfiguration['time_range']['value'];

            $mainClasses = [];
            $mainClasses[] = 'area';
            $mainClasses[] = 'news-' . $fieldConfiguration['layouts']['value'];

            if ($fieldConfiguration['entry_types']['value'] !== 'all') {
                $mainClasses[] = 'entry-type-' . str_replace([
                        '_',
                        ' '
                    ], ['-'], strtolower($fieldConfiguration['entry_types']['value']));
            }

            //finally load query
            $newsObjects = DataObject\NewsEntry::getEntriesPaging($querySettings);

            $subParams = [
                'main_classes'    => implode(' ', $mainClasses),
                'category'        => $fieldConfiguration['category']['value'],
                'show_pagination' => $fieldConfiguration['show_pagination']['value'],
                'entry_type'      => $fieldConfiguration['entry_types']['value'],
                'layout_name'     => $fieldConfiguration['layouts']['value'],
                'paginator'       => $newsObjects,
                'query_settings'  => $querySettings
            ];
        }

        $systemParams = [
            'is_preset_mode' => $isPresetMode,
            //system/editmode related
            'config'         => $fieldConfiguration
        ];

        $params = array_merge($systemParams, $subParams);
        foreach ($params as $key => $value) {
            $view->{$key} = $value;
        }
    }

    /**
     * @param $fieldConfiguration
     * @return bool
     */
    private function isPresetMode($fieldConfiguration)
    {
        return $fieldConfiguration['presets']['value'] !== 'none'
            && $this->presetRegistry->has($fieldConfiguration['presets']['value']);
    }

    /**
     * Set Configuration and set defaults to view fields if they're empty.
     */
    private function setDefaultFieldValues()
    {
        $adminSettings = [];

        $listConfig = $this->configuration->getConfig('list');

        //set presets
        $presetData = $this->getPresetsStore();
        $adminSettings['presets'] = [
            'store' => $presetData['store'],
            'value' => 'none',
            'info'  => $presetData['info']
        ];

        $presetElement = $this->getDocumentField('select', 'presets');
        // if value is empty or service has been removed, reset element.
        if ($presetElement->isEmpty() || count($presetData['store']) === 1) {
            $presetElement->setDataFromResource($adminSettings['presets']['value']);
        } else {
            $adminSettings['presets']['value'] = $presetElement->getData();
        }

        //set latest
        $adminSettings['latest'] = ['value' => (bool)$this->getDocumentField('checkbox', 'latest')->getData()];

        //category
        $hrefConfig = [
            'types'    => ['object'],
            'subtypes' => ['object' => ['object']],
            'classes'  => ['NewsCategory'],
            'width'    => '95%'
        ];

        $adminSettings['category'] = ['value' => null, 'href_config' => $hrefConfig];
        $categoryElement = $this->getDocumentField('href', 'category');
        if (!$categoryElement->isEmpty()) {
            $adminSettings['category']['value'] = $categoryElement->getElement();
        }

        //subcategories
        $adminSettings['include_subcategories'] = ['value' => (bool)$this->getDocumentField('checkbox', 'includeSubCategories')->getData()];

        //single objects
        $multiHrefConfig = [
            'types'    => ['object'],
            'subtypes' => ['object' => ['object']],
            'classes'  => ['NewsEntry'],
            'width'    => '510px',
            'height'   => '150px'
        ];

        $adminSettings['single_objects'] = ['value' => [], 'multi_href_config' => $multiHrefConfig];
        $singleObjectsElement = $this->getDocumentField('multihref', 'singleObjects');
        if (!$singleObjectsElement->isEmpty()) {
            $adminSettings['single_objects']['value'] = $singleObjectsElement->getElements();
        }

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

        //set offset
        $adminSettings['offset'] = ['value' => 0];
        $offsetElement = $this->getDocumentField('numeric', 'offset');
        if ($offsetElement->isEmpty()) {
            $offsetElement->setDataFromResource($adminSettings['offset']['value']);
        } else {
            $adminSettings['offset']['value'] = (int)$offsetElement->getData();
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

    /**
     * @return array
     */
    private function getPresetsStore()
    {
        $data = [
            'store' => [
                ['none', $this->translator->trans('news.no_preset', [], 'admin')]
            ],
            'info'  => []
        ];

        $services = $this->presetRegistry->getList();

        foreach ($services as $alias => $service) {
            $name = $this->translator->trans($service->getName(), [], 'admin');
            $description = !empty($service->getDescription())
                ? $this->translator->trans($service->getDescription(), [], 'admin')
                : null;

            $data['store'][] = [$alias, $name];
            $data['info'][] = ['name' => $alias, 'description' => $description];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getLayoutStore()
    {
        $listConfig = $this->configuration->getConfig('list');

        $store = [];
        foreach ($listConfig['layouts']['items'] as $index => $item) {
            $store[] = [$index, $this->translator->trans($item['name'], [], 'admin')];
        }

        return $store;
    }

    /**
     * @return array
     */
    private function getSortByStore()
    {
        $listConfig = $this->configuration->getConfig('list');

        $store = [];
        foreach ($listConfig['sort_by_store'] as $key => $value) {
            $store[] = [$key, $this->translator->trans($value, [], 'admin')];
        }

        return $store;
    }

    /**
     * @return array
     */
    private function getOrderByStore()
    {
        return [
            ['desc', $this->translator->trans('news.order_by.descending', [], 'admin')],
            ['asc', $this->translator->trans('news.order_by.ascending', [], 'admin')]
        ];
    }

    /**
     * @return array
     */
    private function getTimeRangeStore()
    {
        return [
            ['all', $this->translator->trans('news.time_range.all_entries', [], 'admin')],
            ['current', $this->translator->trans('news.time_range.current_entries', [], 'admin')],
            ['past', $this->translator->trans('news.time_range.past_entries', [], 'admin')]
        ];
    }

    /**
     * @return array
     */
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

    /**
     * @param $type
     * @param $inputName
     * @return null|Document\Tag
     */
    private function getDocumentField($type, $inputName)
    {
        return $this->getDocumentTag($this->document, $type, $inputName);
    }

    /**
     * @return bool
     */
    public function hasEditTemplate()
    {
        return true;
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