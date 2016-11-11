# Pimcore News
Pimcore News Plugin

## Requirements
* Pimcore 4.3

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