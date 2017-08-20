<?php

namespace NewsBundle\CoreExtension;

use Pimcore\Model\Object\ClassDefinition\Data\Select;

class NewsTypeSelect extends Select
{
    /**
     * Static type of this element
     * @var string
     */
    public $fieldtype = 'newsTypeSelect';

}
