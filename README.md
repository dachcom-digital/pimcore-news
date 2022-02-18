# Pimcore News
Pimcore News Bundle. Generate simple [custom entry types](docs/20_EntryTypes.md) like Events, Press or Event.

## Requirements
⚠️⚠️ This bundle exists for legacy purposes only and **will not** be developed further.

## Installation

```json
"require" : {
    "dachcom-digital/news" : "~3.0.0",
}
```

- Execute: `$ bin/console pimcore:bundle:enable NewsBundle`
- Execute: `$ bin/console pimcore:bundle:install NewsBundle`

## Important to know
- The detail url is based on the title for each language. If the detail url field is empty, the title will be transformed to a valid url slug.
- The News Bundle will install two classes (`NewsEntry` and `NewsCategory`). If you're going to modify them, please make sure that you're follow our [upgrade notes](UPGRADE.md) in case we're changing the class structure.

## Good to know
- News can be placed at any place on your website through the news area element. Use it as list, latest or even as a custom layout.
- The detail page always stays the same (see static route), no matter where you placed the area element.
- It's possible to override the detail url in the news object.

## Extending Entry Object
- *Meta Information* Tab: Extend Entries with [classification store](https://www.pimcore.org/docs/latest/Objects/Object_Classes/Data_Types/Classification_Store.html) data.
- *Relations & Settings* Tab: Extend Entries with [Object Bricks](https://www.pimcore.org/docs/latest/Objects/Object_Classes/Data_Types/Object_Bricks.html).
- Configure additional configuration fields with an [eventlistener and twig](./docs/40_CustomConfiguration.md)

### Further Information
- [Head Meta Generator](./docs/10_HeadMetaGenerator.md)
- [Link Generator](./docs/11_LinkGenerator.md)
- [Related Entries Generator](./docs/12_RelatedEntriesGenerator.md)
- [The Amazing Entry Types](./docs/20_EntryTypes.md)
- [Presets](./docs/30_Presets.md) (new!)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
