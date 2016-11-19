# Pimcore News
Pimcore News Plugin

## Requirements
* Pimcore 4.3

## Installation
**Handcrafted Installation**   
1. Download Plugin  
2. Rename it to `News`  
3. Place it in your plugins directory  
4. Activate & install it through backend 

**Composer Installation**  
1. Add code below to your `composer.json`  
2. Activate & install it through backend

```json
"require" : {
    "dachcom-digital/pimcore-news" : "1.1.4",
}
```

## Important (!) to know
* The detail url is based on the title for each language. So if you update your title, the url to your news will be updated also.

## Good to know
* News can be placed at any place on your website through the news area element. Use it as list, latest or even as a custom layout.
* The detail page always stays the same (see static route), no matter where you placed the area element.
* It's possible to override the detail url in the news object.

## Extend News  
**Data**  

* *Meta Information* Tab: Extend News with [classification store](https://www.pimcore.org/docs/latest/Objects/Object_Classes/Data_Types/Classification_Store.html) data.  
* *Relations & Settings* Tab: Extend News with [Object Bricks](https://www.pimcore.org/docs/latest/Objects/Object_Classes/Data_Types/Object_Bricks.html).  

**Override Templates**  
To override the news scripts, just create a news folder in your scripts folder to override templates:
 
 `/website/views/scripts/news/detail.php`
 
## Events
**news.head.meta**  
Use this event to override news detail meta tags.

```
$params = [
    'title'             => $title,
    'description'       => $description,
    'og:title'          => $ogTitle,
    'og:description'    => $ogDescription,
    'og:url'            => $ogDescription,
    'og:image'          => $ogImage
];
        
```
**news.detail.url**  
Use this event to manipulate the news detail url.

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
