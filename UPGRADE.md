# Upgrade Notes

#### Update from Version 1.3.x to Version 1.4.0

**Entry Types**  
We've added the powerful entry types (see [#5](https://github.com/dachcom-digital/pimcore-news/issues/5)). Please check out our readme to get more information about this feature.

Add this to your `news_configuration.php`:
```php
5 => [
    "id" => 5,
    "key" => "entry_types",
    "data" => [
        "news" => [
            "name" => "News",
            "route" => "news_detail",
            "customLayoutId" => NULL
        ]
    ],
    "creationDate" => 1491494099,
    "modificationDate" => 1491494099
]
```
- You also need to update or migrate both classes from `install/object/structures/*.json`.
- Remove the `press_detail static route if you're using just one entry type.

**Misc**  
- Parent category has been removed (see [#10](https://github.com/dachcom-digital/pimcore-news/issues/10)), please check your installation first:
    - check your objects tables from `parentCategory__id` against `o_parentId`.
- Re-import `translations/data.csv`
- We've removed the area `.pimcore_area_news.pimcore_area_content` element wrapper. check your css before updating.

#### Update from Version 1.2.x to Version 1.3.0
- Re-import classes from `install/object/structures`!

#### Update from Version 1.1.4 to Version 1.2.0
- Update the [static route](install/staticroutes.xml)!
- Re-import classes from `install/object/structures`!
- use Carbon Date (instead of `$news->getDate()->get('dd.MM.YYYY');` use `$news->getDate()->format('d.m.Y');`)
- Do not use the url helper for creating news detail urls. just use the view helper instead:
- Please not that the generation of seo urls has changed (better handling of foreign languages)

```
$url = $this->newsHelper()->getDetailUrl( $this->news );
```