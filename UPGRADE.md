# Upgrade Notes

## Migrating from Version 2.x to Version 3.0.0

### Global Changes
- PHP8 return type declarations added: you may have to adjust your extensions accordingly
- View `Areas/news/edit_custom.html.twig` has been removed. Use Event `\NewsBundle\NewsEvents::NEWS_EDITABLE_DIALOG` instead
- It is no longer possible to toggle editable configuration based on given values. 
***

News 2.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-news/blob/2.x/UPGRADE.md
