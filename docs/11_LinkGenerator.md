# Link Generator
The Link Generator creates a valid detail and back link for each entry.

# Pimcore Link Generator
If you want to use the default Pimcore [Link Generator](https://pimcore.com/docs/5.x/Development_Documentation/Objects/Object_Classes/Class_Settings/Link_Generator.html),
just implement it like described in the link.
If no default Link Generator Service has been defined, the News Bundle will proceed with the internal Class.

## Create a custom generator
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