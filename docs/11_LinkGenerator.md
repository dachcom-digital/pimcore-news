# Link Generator
The Link Generator creates a valid detail and back link for each entry.

## Create a custom Generator
Just override the build in generator:

```yaml
news:
    relations:
        NewsBundle\Generator\LinkGenerator: AppBundle\Generator\AppLinkGenerator
```

And set up your new class:

```php
<?php

namespace AppBundle\Generator;

use NewsBundle\Model\EntryInterface;
use NewsBundle\Generator\LinkGenerator;

class AppLinkGenerator extends LinkGenerator
{
    public function generateDetailLink(EntryInterface $entry, $additionalUrlParams = [])
    {
        $absPath = 'your_overridden_abs_path_to_entry';
        return $absPath;
    }
}
```