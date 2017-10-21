<?php

namespace News\Model;

use Pimcore\Model;

class AdminStyle extends \Pimcore\Model\Element\AdminStyle
{
    /**
     * @var Model\Object\AbstractObject
     */
    protected $element;

    /**
     * AdminStyle constructor.
     *
     * @param Model\Object\Concrete $element
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
        if ($this->element instanceof Model\Object\NewsEntry) {
            return [
                'title' => 'ID: ' . $this->element->getId(),
                'text'  => $this->element->getClass()->getName() . ' | Type: ' . $this->element->getEntryType()
            ];
        }

        return parent::getElementQtipConfig();
    }
}
