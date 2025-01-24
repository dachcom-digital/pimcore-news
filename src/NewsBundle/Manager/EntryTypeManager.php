<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace NewsBundle\Manager;

use NewsBundle\Configuration\Configuration;
use Pimcore\Bundle\StaticRoutesBundle\Model\Staticroute;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Http\Request\Resolver\EditmodeResolver;
use Pimcore\Http\Request\Resolver\SiteResolver;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Site;
use Pimcore\Tool;
use Pimcore\Translation\Translator;

class EntryTypeManager
{
    protected Configuration $configuration;
    protected Translator $translator;
    protected SiteResolver $siteResolver;
    protected EditmodeResolver $editmodeResolver;
    protected DocumentResolver $documentResolver;
    protected array $routeData = [];

    public function __construct(
        Configuration $configuration,
        Translator $translator,
        SiteResolver $siteResolver,
        EditmodeResolver $editmodeResolver,
        DocumentResolver $documentResolver
    ) {
        $this->configuration = $configuration;
        $this->translator = $translator;
        $this->siteResolver = $siteResolver;
        $this->editmodeResolver = $editmodeResolver;
        $this->documentResolver = $documentResolver;
    }

    public function getTypes(mixed $object = null): array
    {
        $entryTypes = $this->getTypesFromConfig();

        $validLayouts = null;
        $masterLayoutAvailable = false;
        if (!is_null($object)) {
            $validLayouts = DataObject\Service::getValidLayouts($object);
            if (array_key_exists(0, $validLayouts)) {
                $masterLayoutAvailable = true;
            }
        }

        foreach ($entryTypes as $typeId => &$type) {
            if (!array_key_exists('custom_layout_id', $type) || $type['custom_layout_id'] === 0) {
                $type['custom_layout_id'] = null;
            }

            $customLayoutId = $type['custom_layout_id'];
            //if string (name) is given, get layout via listing
            if (is_string($customLayoutId)) {
                $customLayout = ClassDefinition\CustomLayout::getByName($type['custom_layout_id']);
                if ($customLayout instanceof DataObject\ClassDefinition\CustomLayout) {
                    $customLayoutId = (int) $customLayout->getId();
                } else {
                    $customLayoutId = 0; //reset layout to default -> custom layout is not available!
                }
            }

            //remove types if valid layout is set and user is not allowed to use it!
            if (!is_null($customLayoutId)) {
                // custom layout found: check if user has rights to use it! if not: remove from selection!
                if (!is_null($validLayouts) && $masterLayoutAvailable === false && !isset($validLayouts[$customLayoutId])) {
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

    public function getDefaultType(): string
    {
        $entryTypeConfig = $this->configuration->getConfig('entry_types');

        return $entryTypeConfig['default'];
    }

    public function getTypeInfo(string $typeName): array
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

    public function getTypesFromConfig(): array
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

    public function getRouteInfo(string $entryType): array
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

        $site = null;
        if (!$this->editmodeResolver->isEditmode()) {
            if ($this->siteResolver->isSiteRequest()) {
                $site = $this->siteResolver->getSite();
            }
        } else {
            $currentDocument = $this->documentResolver->getDocument();
            $site = Tool\Frontend::getSiteForDocument($currentDocument);
        }

        $routeData['site'] = null;
        if ($site instanceof Site) {
            $routeData['site'] = $site->getId();
        }

        $route = Staticroute::getByName($routeData['name'], $routeData['site']);

        if (empty($route)) {
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
