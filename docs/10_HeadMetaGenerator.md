# Head Meta Generator
The Head Meta Generator creates some additional meta information for the news detail page (like head title, meta description and some open graph elements).

## Create a custom Generator
Just override the build in generator:

```yaml
news.generator.head_meta:
    parent: news.generator.abstract.head_meta
    class: AppBundle\Generator\HeadMetaGenerator
```

And set up your new class:

```php
<?php

namespace AppBundle\Generator;

use NewsBundle\Model\EntryInterface;
use NewsBundle\Generator\AbstractHeadMetaGenerator;

class HeadMetaGenerator extends AbstractHeadMetaGenerator
{
    public function generateMeta(EntryInterface $entry): array
    {
        //always use the link generator to build a valid link
        $href = $this->linkGenerator->generateDetailLink($entry);
        $params = ['your_meta_tag_name' => $href];
        return $params;
    }
}
```