# Upgrade Notes

#### Update from Version 1.2.x to Version 1.3.x
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