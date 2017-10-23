<?php

namespace NewsBundle\Manager;

use NewsBundle\Configuration\Configuration;
use Pimcore\Model\Site;
use Pimcore\Model\Staticroute;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Translation\TranslatorInterface;

class EntryTypeManager
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $routeData = [];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * EntryTypeManager constructor.
     *
     * @param Configuration $configuration
     * @param TranslatorInterface $translator
     */
    public function __construct(Configuration $configuration, TranslatorInterface $translator)
    {
        $this->configuration = $configuration;
        $this->translator = $translator;
    }

    /**
     * @param null $object
     *
     * @return array|mixed|null
     */
    public function getTypes($object = NULL)
    {
        $entryTypes = $this->getTypesFromConfig();

        $validLayouts = NULL;
        $masterLayoutAvailable = FALSE;
        if (!is_null($object)) {
            $validLayouts = DataObject\Service::getValidLayouts($object);
            if(array_key_exists(0, $validLayouts)) {
                $masterLayoutAvailable = TRUE;
            }
        }

        foreach ($entryTypes as $typeId => &$type) {
            if($type['custom_layout_id'] === 0) {
                $type['custom_layout_id'] = NULL;
            }

            $customLayoutId = $type['custom_layout_id'];
            //if string (name) is given, get layout via listing
            if (is_string($customLayoutId)) {
                $list = new ClassDefinition\CustomLayout\Listing();
                $list->setLimit(1);
                $list->setCondition('name = ?', $type['custom_layout_id']);
                $list = $list->load();
                if (isset($list[0]) && $list[0] instanceof DataObject\ClassDefinition\CustomLayout) {
                    $customLayoutId = (int)$list[0]->getId();
                } else {
                    $customLayoutId = 0; //reset layout to default -> custom layout is not available!
                }
            }

            //remove types if valid layout is set and user is not allowed to use it!
            if(!is_null($customLayoutId)) {
                // custom layout found: check if user has rights to use it! if not: remove from selection!
                if(!is_null($validLayouts) && $masterLayoutAvailable === FALSE && !isset($validLayouts[$customLayoutId])) {
                    unset($entryTypes[$typeId]);
                } else {
                    $type['custom_layout_id'] = $customLayoutId;
                }
            } else {
                $type['custom_layout_id'] = 0;
            }
        }

        return $entryTypes;
    }

    /**
     * Get Default Entry Type
     * @return mixed
     */
    public function getDefaultType()
    {
        $entryTypeConfig = $this->configuration->getConfig('entry_types');
        return $entryTypeConfig['default'];
    }

    /**
     * @param $typeName
     * @return array|mixed
     */
    public function getTypeInfo($typeName)
    {
        $info = [];
        $types = $this->getTypes();

        if (isset($types[$typeName])) {
            $info = $types[$typeName];
            //translate name.
            $info['name'] = $this->translator->trans($types[$typeName]['name'], [], 'admin');
        }

        return $info;
    }

    /**
     * @return array|mixed|null
     */
    public function getTypesFromConfig()
    {
        $entryTypeConfig = $this->configuration->getConfig('entry_types');

        $types = $entryTypeConfig['items'];

        //cannot be empty - at least "news" is required.
        if (empty($types)) {
            $types = [
                'news' => [
                    'name'           => 'news.entry_type.news',
                    'route'          => '',
                    'customLayoutId' => 0
                ]
            ];
        }

        return $types;
    }

    /**
     * @param $entryType
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getRouteInfo($entryType)
    {
        //use cache.
        if (isset($this->routeData[$entryType])) {
            return $this->routeData[$entryType];
        }

        $routeData = ['name' => 'news_detail', 'urlParams' => []];
        $types = $this->getTypesFromConfig();

        if (isset($types[$entryType]) && !empty($types[$entryType]['route'])) {
            $routeData['name'] = $types[$entryType]['route'];
        }

        $siteId = NULL;
        if (Site::isSiteRequest()) {
            $siteId = Site::getCurrentSite()->getId();
        }

        $route = Staticroute::getByName($routeData['name'], $siteId);

        if(empty($route)) {
            throw new \Exception(sprintf('"%s" route is not available. please add it to your static routes', $routeData['name']));
        }
        $variables = explode(',', $route->getVariables());

        //remove default one
        $defaults = ['news'];
        $variables = array_diff($variables, $defaults);

        $routeData['urlParams'] = array_merge($routeData['urlParams'], $variables);
        $this->routeData[$entryType] = $routeData;

        return $routeData;
    }
}