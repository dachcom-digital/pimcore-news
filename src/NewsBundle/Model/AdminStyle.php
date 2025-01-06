<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
