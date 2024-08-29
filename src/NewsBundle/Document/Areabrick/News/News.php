<?php

namespace NewsBundle\Document\Areabrick\News;

use NewsBundle\Event\NewsBrickEvent;
use NewsBundle\NewsEvents;
use NewsBundle\Registry\PresetRegistry;
use Pimcore\Extension\Document\Areabrick\AbstractAreabrick;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Response;
use NewsBundle\Configuration\Configuration;
use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class News extends AbstractAreabrick implements EditableDialogBoxInterface
{
    protected Document\PageSnippet $document;
    protected Configuration $configuration;
    protected EntryTypeManager $entryTypeManager;
    protected Translator $translator;
    protected PresetRegistry $presetRegistry;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        Configuration $configuration,
        EntryTypeManager $entryTypeManager,
        Translator $translator,
        PresetRegistry $presetRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->entryTypeManager = $entryTypeManager;
        $this->translator = $translator;
        $this->presetRegistry = $presetRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getEditableDialogBoxConfiguration(Document\Editable $area, ?Document\Editable\Area\Info $info): EditableDialogBoxConfiguration
    {
        $this->document = $area->getDocument();

        $editableDialog = new EditableDialogBoxConfiguration();
        $editableDialog->setWidth(600);
        $editableDialog->setHeight(600);
        $editableDialog->setReloadOnClose(true);

        $fieldConfiguration = $this->getDefaultFieldValues();

        $firstTabItems = [];
        $secondTabItems = [];
        $thirdTabItems = [];

        // first element is always "none"
        if (count($fieldConfiguration['presets']['store']) > 1) {
            $firstTabItems[] = [
                'type'   => 'select',
                'label'  => $this->translator->trans('news.preset', [], 'admin'),
                'name'   => 'presets',
                'config' => [
                    'width' => 250,
                    'store' => $fieldConfiguration['presets']['store']
                ]
            ];
        }

        $firstTabItems[] = [
            'type'   => 'select',
            'label'  => $this->translator->trans('news.layout', [], 'admin'),
            'name'   => 'layout',
            'config' => [
                'width' => 250,
                'store' => $fieldConfiguration['layouts']['store']
            ]
        ];

        $firstTabItems[] = [
            'type'   => 'select',
            'label'  => $this->translator->trans('news.entry_type', [], 'admin'),
            'name'   => 'entryType',
            'config' => [
                'width' => 250,
                'store' => $fieldConfiguration['entry_types']['store']
            ]
        ];

        $firstTabItems[] = [
            'type'   => 'select',
            'label'  => $this->translator->trans('news.sort_by', [], 'admin'),
            'name'   => 'sortby',
            'config' => [
                'width' => 250,
                'store' => $fieldConfiguration['sort_by']['store']
            ]
        ];

        $firstTabItems[] = [
            'type'   => 'select',
            'label'  => $this->translator->trans('news.order_by', [], 'admin'),
            'name'   => 'orderby',
            'config' => [
                'width' => 250,
                'store' => $fieldConfiguration['order_by']['store']
            ]
        ];

        $firstTabItems[] = [
            'type'   => 'select',
            'label'  => $this->translator->trans('news.time_range', [], 'admin'),
            'name'   => 'timeRange',
            'config' => [
                'width' => 250,
                'store' => $fieldConfiguration['time_range']['store']
            ]
        ];

        $firstTabItems[] = [
            'type'   => 'relation',
            'label'  => $this->translator->trans('news.category', [], 'admin'),
            'name'   => 'category',
            'config' => $fieldConfiguration['category']['relation_config']
        ];

        $firstTabItems[] = [
            'type'   => 'checkbox',
            'label'  => $this->translator->trans('news.include_subcategories', [], 'admin'),
            'name'   => 'includeSubCategories',
            'config' => []
        ];

        $firstTabItems[] = [
            'type'   => 'relations',
            'label'  => $this->translator->trans('news.single_objects', [], 'admin'),
            'name'   => 'singleObjects',
            'config' => $fieldConfiguration['single_objects']['relations_config']
        ];


        $secondTabItems[] = [
            'type'   => 'numeric',
            'label'  => $this->translator->trans('news.entries_per_page', [], 'admin'),
            'name'   => 'itemsPerPage',
            'config' => [
                'decimalPrecision' => 0,
                'minValue'         => 0
            ]
        ];

        $secondTabItems[] = [
            'type'   => 'numeric',
            'label'  => $this->translator->trans('news.maximum_number_of_entries', [], 'admin'),
            'name'   => 'limit',
            'config' => [
                'decimalPrecision' => 0,
                'minValue'         => 0
            ]
        ];

        $secondTabItems[] = [
            'type'   => 'numeric',
            'label'  => $this->translator->trans('news.offset', [], 'admin'),
            'name'   => 'offset',
            'config' => [
                'decimalPrecision' => 0,
                'minValue'         => 0
            ]
        ];

        $thirdTabItems[] = [
            'type'   => 'checkbox',
            'label'  => $this->translator->trans('news.show_only_top_entries', [], 'admin'),
            'name'   => 'latest',
            'config' => []
        ];

        $thirdTabItems[] = [
            'type'   => 'checkbox',
            'label'  => $this->translator->trans('news.show_pagination', [], 'admin'),
            'name'   => 'showPagination',
            'config' => []
        ];

        $tabbedItems = [];

        $tabbedItems[] = [
            'type'     => 'panel',
            'title'    => $this->translator->trans('news.editable.first_tab', [], 'admin'),
            'items'    => $firstTabItems
        ];

        $tabbedItems[] = [
            'type'     => 'panel',
            'title'    => $this->translator->trans('news.editable.second_tab', [], 'admin'),
            'items'    => $secondTabItems
        ];

        $tabbedItems[] = [
            'type'     => 'panel',
            'title'    => $this->translator->trans('news.editable.third_tab', [], 'admin'),
            'items'    => $thirdTabItems
        ];

        $editableDialog->setItems([
            'type'  => 'tabpanel',
            'items' => $tabbedItems
        ]);

        $event = new GenericEvent($editableDialog);

        $this->eventDispatcher->dispatch($event, NewsEvents::NEWS_EDITABLE_DIALOG);

        return $event->getSubject();
    }

    public function action(Document\Editable\Area\Info $info): ?Response
    {
        $this->document = $info->getDocument();

        $fieldConfiguration = $this->getDefaultFieldValues();

        $isPresetMode = false;
        $presetParams = [];

        //check if preset has been selected at first
        if($this->isPresetMode($fieldConfiguration)) {
            $isPresetMode = true;
            $preset = $this->presetRegistry->get($fieldConfiguration['presets']['value']);
            $preset->setInfo($info);

            $presetParams['preset_name'] = $fieldConfiguration['presets']['value'];
            $presetParams['preset'] = [];
            foreach ($preset->getViewParams() as $key => $param) {
                $presetParams['preset'][$key] = $param;
            }
        }

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
        $querySettings['page'] = (int) $info->getRequest()->query->get('page');

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

        $event = new NewsBrickEvent($info, $querySettings);
        $this->eventDispatcher->dispatch($event, NewsEvents::NEWS_BRICK_QUERY_BUILD);

        $querySettings = $event->getQuerySettings();
        $additionalViewParams = $event->getAdditionalViewParams();

        $newsObjects = DataObject\NewsEntry::getEntriesPaging($querySettings);

        $subParams = array_merge($presetParams, [
            'main_classes'           => implode(' ', $mainClasses),
            'category'               => $fieldConfiguration['category']['value'],
            'show_pagination'        => $fieldConfiguration['show_pagination']['value'],
            'entry_type'             => $fieldConfiguration['entry_types']['value'],
            'layout_name'            => $fieldConfiguration['layouts']['value'],
            'paginator'              => $newsObjects,
            'additional_view_params' => $additionalViewParams,
            'query_settings'         => $querySettings
        ]);

        $systemParams = [
            'is_preset_mode' => $isPresetMode,
            'config'         => $fieldConfiguration
        ];

        foreach (array_merge($systemParams, $subParams) as $key => $value) {
            $info->setParam($key, $value);
        }

        return null;
    }

    private function isPresetMode(array $fieldConfiguration): bool
    {
        return $fieldConfiguration['presets']['value'] !== 'none'
            && $this->presetRegistry->has($fieldConfiguration['presets']['value']);
    }

    private function getDefaultFieldValues(): array
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
        $adminSettings['latest'] = ['value' => (bool) $this->getDocumentField('checkbox', 'latest')->getData()];

        //category
        $adminSettings['category'] = [
            'value'           => null,
            'relation_config' => [
                'types'    => ['object'],
                'subtypes' => ['object' => ['object']],
                'classes'  => ['NewsCategory']
            ]
        ];

        $categoryElement = $this->getDocumentField('relation', 'category');
        if (!$categoryElement->isEmpty()) {
            $adminSettings['category']['value'] = $categoryElement->getElement();
        }

        //subcategories
        $adminSettings['include_subcategories'] = [
            'value' => (bool) $this->getDocumentField('checkbox', 'includeSubCategories')->getData()
        ];

        //single objects
        $adminSettings['single_objects'] = [
            'value'            => [],
            'relations_config' => [
                'types'    => ['object'],
                'subtypes' => ['object' => ['object']],
                'classes'  => ['NewsEntry']
            ]
        ];

        $singleObjectsElement = $this->getDocumentField('relations', 'singleObjects');
        if (!$singleObjectsElement->isEmpty()) {
            $adminSettings['single_objects']['value'] = $singleObjectsElement->getElements();
        }

        //show pagination
        $adminSettings['show_pagination'] = ['value' => (bool) $this->getDocumentField('checkbox', 'showPagination')->getData()];

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
            $itemsPerPageElement->setDataFromResource((string) $adminSettings['paginate']['items_per_page']['value']);
        } else {
            $adminSettings['paginate']['items_per_page']['value'] = (int) $itemsPerPageElement->getData();
        }

        //set limit
        $adminSettings['max_items'] = ['value' => $listConfig['max_items']];
        $limitElement = $this->getDocumentField('numeric', 'limit');
        if ($limitElement->isEmpty() || $itemsPerPageElement->getData() < 0) {
            $limitElement->setDataFromResource((string) $adminSettings['max_items']['value']);
        } else {
            $adminSettings['max_items']['value'] = (int) $limitElement->getData();
        }

        //set offset
        $adminSettings['offset'] = ['value' => 0];
        $offsetElement = $this->getDocumentField('numeric', 'offset');
        if ($offsetElement->isEmpty()) {
            $offsetElement->setDataFromResource((string) $adminSettings['offset']['value']);
        } else {
            $adminSettings['offset']['value'] = (int) $offsetElement->getData();
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

    private function getPresetsStore(): array
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

    private function getLayoutStore(): array
    {
        $listConfig = $this->configuration->getConfig('list');

        $store = [];
        foreach ($listConfig['layouts']['items'] as $index => $item) {
            $store[] = [$index, $this->translator->trans($item['name'], [], 'admin')];
        }

        return $store;
    }

    private function getSortByStore(): array
    {
        $listConfig = $this->configuration->getConfig('list');

        $store = [];
        foreach ($listConfig['sort_by_store'] as $key => $value) {
            $store[] = [$key, $this->translator->trans($value, [], 'admin')];
        }

        return $store;
    }

    private function getOrderByStore(): array
    {
        return [
            ['desc', $this->translator->trans('news.order_by.descending', [], 'admin')],
            ['asc', $this->translator->trans('news.order_by.ascending', [], 'admin')]
        ];
    }

    private function getTimeRangeStore(): array
    {
        return [
            ['all', $this->translator->trans('news.time_range.all_entries', [], 'admin')],
            ['current', $this->translator->trans('news.time_range.current_entries', [], 'admin')],
            ['past', $this->translator->trans('news.time_range.past_entries', [], 'admin')]
        ];
    }

    private function getEntryTypeStore(): array
    {
        $store = [
            ['all', $this->translator->trans('news.entry_type.all', [], 'admin')]
        ];

        foreach ($this->entryTypeManager->getTypesFromConfig() as $typeKey => $typeData) {
            $store[] = [$typeKey, $this->translator->trans($typeData['name'], [], 'admin')];
        }

        return $store;
    }

    private function getDocumentField($type, $inputName): Document\Editable\EditableInterface
    {
        return $this->getDocumentEditable($this->document, $type, $inputName);
    }

    public function hasEditTemplate(): bool
    {
        return true;
    }

    public function getTemplate(): string
    {
        return sprintf('@News/Areas/news/view.%s', $this->getTemplateSuffix());
    }

    public function getTemplateLocation(): string
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }

    public function getTemplateSuffix(): string
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    public function getName(): string
    {
        return 'News';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getHtmlTagOpen(Document\Editable\Area\Info $info): string
    {
        return '';
    }

    public function getHtmlTagClose(Document\Editable\Area\Info $info): string
    {
        return '';
    }

}
