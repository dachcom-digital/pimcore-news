<?php

namespace NewsBundle\Model;

use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Model;

class AdminStyle extends Model\Element\AdminStyle
{
    protected Model\DataObject\AbstractObject $element;

    public function __construct($element)
    {
        $this->element = $element;

        parent::__construct($element);
    }

    public function getElementQtipConfig(): array
    {
        $entryTypeManager = \Pimcore::getContainer()->get(EntryTypeManager::class);
        $info = $entryTypeManager->getTypeInfo($this->element->getEntryType());
        $name = $info['name'] ?? '--';

        if ($this->element instanceof Model\DataObject\NewsEntry) {
            return [
                'title' => 'ID: ' . $this->element->getId(),
                'text'  => $this->element->getClass()->getName() . ' | Type: ' . $name
            ];
        }

        return parent::getElementQtipConfig();
    }
}
