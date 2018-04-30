# Presets
A preset is completely independent and allows you to create custom queries and views.

![preview](http://g.recordit.co/CroZQHo0xQ.gif)

## Use Case
By default your customer wants to add entries with different settings.
He's able to do so with the default parameters available in the edit window.
But sometimes there are also special use cases. For example:

- Show two news and one event entry in one special view
- Show one top news and two dedicated events

For that you would use presets:
A preset allows you to create a custom list of entries and also a custom view to display your data.

## Create a new Preset
Learn how to create a custom preset.

### Configuration
First we need to register a new preset:

```yml
AppBundle\Services\NewsBundle\Preset\SpecialPreset:
    public: false
    tags:
        - { name: news.preset, alias: special }
```

### Service
Next we need to create the preset service itself:

```php
<?php

namespace AppBundle\Services\NewsBundle\Preset;

use NewsBundle\Preset\PresetInterface;
use Pimcore\Model\DataObject\NewsEntry;
use Pimcore\Model\Document\Tag\Area\Info;
use Zend\Paginator\Paginator;

class SpecialPreset implements PresetInterface
{
    protected $info;

    /**
     * Returns the name for drop down selection.
     * This value gets applied via translation engine
     * so use a good translation string like "news.preset.my_preset_name
     *
     * @return string
     */
    public function getName()
    {
        return 'news.preset.special_preset';
    }

    /**
     * Returns a description for drop down selection.
     * This value gets applied via translation engine
     * so use a good translation string like "news.preset.my_preset_description
     * Return NULL if you don't want to provide any description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return 'news.preset.special_preset_description';
    }

    /**
     * Every preset comes with the Area Info
     *
     * @param Info $info
     */
    public function setInfo(Info $info)
    {
        $this->info = $info;
    }

    /**
     * This method needs to return a key value array.
     *
     * @return array
     */
    public function getViewParams(): array
    {
        // this is just an example,
        // just return any parameter you need in your view
        $list = NewsEntry::getList();
        $list->setLimit(5);

        $paginator = new Paginator($list);
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(2);

        return [
            'paginator' => $paginator
        ];
    }
}
```

## Twig View
Add a view file to `app/Resources/NewsBundle/views/List/Preset/special.html.twig`:
> **Note:** The view name has to be the same as your defined service.
> **Note:** All your parameters are stored in the `preset` variable.

```twig
<h1>My Special View</h1>

{% for news in preset.paginator %}
    <div class="news-entry">
        <h2>{{ news.getName }}</h2>
    </div>
{% endfor %}

{# paginator example #}
{% include '@News/List/Block/paginator.html.twig' with { paginator: preset.paginator } only %}
```