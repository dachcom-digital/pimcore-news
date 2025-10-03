# Upgrade Notes

## 4.2.0
- [CHORE] Update codebase for Pimcore 11.4 compatibility; add `AdminStyleListener` and bind `image_thumbnails` parameter
- [BUGFIX] Replace `in_array` with `array_key_exists` in `getNewsThumbnail` twig extension `news_thumbnail`
## 4.1.3
- [BUGFIX] Fix Translation Service Injection
- [BUGFIX] Omit passing null value to substr method
## 4.1.2
- [BUGFIX] Fix Poster Property Assignment
## 4.1.1
- [BUGFIX] Fix Custom Layout Loader
## 4.1.0
- [LICENSE] Dual-License with GPL and Dachcom Commercial License (DCL) added
## 4.0.2
- [BUGFIX] remove `o_`-Prefixes for object columns [#71](https://github.com/dachcom-digital/pimcore-news/issues/71)
## 4.0.1
- [IMPROVEMENT] Update Typehint in News areabrick to be more specific [#69](https://github.com/dachcom-digital/pimcore-news/issues/69)
  - If you have overwritten the `News` areabrick, please check you setup and make adjustments if necessary.

## Migrating from Version 3.x to Version 4.0.0

### Global Changes

---

News 3.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-news/blob/3.x/UPGRADE.md
