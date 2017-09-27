# Related Entries Generator
The Related Entries Generator creates a listing for related entries.

## Create a custom Generator
Just override the build in generator:

```yaml
news:
    relations:
        NewsBundle\Generator\RelatedEntriesGenerator: AppBundle\Generator\AppRelatedEntriesGenerator
```

And set up your new class:

```php
<?php

namespace AppBundle\Generator;

use NewsBundle\Model\EntryInterface;
use NewsBundle\Generator\RelatedEntriesGenerator;

class AppRelatedEntriesGenerator extends RelatedEntriesGenerator
{
public function generateRelatedEntries(EntryInterface $news, $params = [])
    {
        $listing = parent::generateRelatedEntries($news, $params);
        return $listing;
    }
}
```