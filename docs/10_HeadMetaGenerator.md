# Head Meta Generator
The Head Meta Generator creates some additional meta information for the news detail page (like head title, meta description and some open graph elements).

## Create a custom Generator
Just override the build in generator:

```yaml

news:
    relations:
        NewsBundle\Generator\HeadMetaGenerator: AppBundle\Generator\AppHeadMetaGenerator
```

And set up your new class:

```php
<?php

namespace AppBundle\Generator;

use NewsBundle\Model\EntryInterface;
use NewsBundle\Generator\HeadMetaGenerator;

class AppHeadMetaGenerator extends HeadMetaGenerator
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