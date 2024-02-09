<?php

namespace NewsBundle\CoreExtension;

use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

class EntryTypeSelect extends Select
{
    public string $fieldtype = 'entryTypeSelect';

    public function getFieldType(): string
    {
        return $this->fieldtype;
    }
}
