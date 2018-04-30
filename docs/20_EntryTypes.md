# Entry Types

![](http://g.recordit.co/i08dUpt52i.gif)

This Bundles allows you to define as many entry types as you want. Entry types could be:

- news
- press
- blog
- events
- ...

Each type comes with some configuration:

- `name`: Set a name for your type. This label will be added to the backend translation.
- `route`: Define a custom static route.
- `custom_layout_id`: Thanks to the pimcore custom layouts, you may want to define a custom layout for your entry type.

**Example**  
```yaml
news:
    entry_types:
        default: 'blog' # the default entry type to start with
            items:
                news:
                    name: 'news.entry_type.news'
                    route: 'news_detail'
                    custom_layout_id: 0
                blog:
                    name: 'news.entry_type.blog'
                    route: 'blog_detail'
                    custom_layout_id: 1
                press:
                    name: 'news.entry_type.press'
                    route: 'blog_detail'
                    custom_layout_id: 1
```
