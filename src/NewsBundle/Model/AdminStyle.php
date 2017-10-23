<?php

namespace NewsBundle\Model;

use NewsBundle\Manager\EntryTypeManager;
use Pimcore\Model;

class AdminStyle extends Model\Element\AdminStyle
{
    /**
     * @var Model\DataObject\AbstractObject
     */
    protected $element;

    /**
     * AdminStyle constructor.
     *
     * @param Model\DataObject\Concrete $element
     */
    public function __construct($element)
    {
        $this->element = $element;
        parent::__construct($element);
    }

    /**
     * @return array
     */
    public function getElementQtipConfig()
    {
        $entryTypeManager = \Pimcore::getContainer()->get(EntryTypeManager::class);
        $info = $entryTypeManager->getTypeInfo($this->element->getEntryType());
        $name = isset($info['name']) ? $info['name'] : '--';
        if ($this->element instanceof Model\DataObject\NewsEntry) {
            return [
                'title' => 'ID: ' . $this->element->getId(),
                'text'  => $this->element->getClass()->getName() . ' | Type: ' . $name
            ];
        }

        return parent::getElementQtipConfig();
    }
}
