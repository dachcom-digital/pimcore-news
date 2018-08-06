<?php

namespace NewsBundle\CoreExtension;

use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

class EntryTypeSelect extends Select
{
    /**
     * Static type of this element
     *
     * @var string
     */
    public $fieldtype = 'entryTypeSelect';

}
