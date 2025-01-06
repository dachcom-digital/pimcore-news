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

namespace NewsBundle\Registry;

use NewsBundle\Preset\PresetInterface;

class PresetRegistry
{
    protected array $registry = [];

    public function register($alias, $service): void
    {
        if (!in_array(PresetInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    PresetInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->registry[$alias] = $service;
    }

    public function has($alias): bool
    {
        return isset($this->registry[$alias]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $alias)
    {
        if (!$this->has($alias)) {
            throw new \Exception('"' . $alias . '" preset does not exist');
        }

        return $this->registry[$alias];
    }

    /**
     * @return PresetInterface[]
     */
    public function getList()
    {
        return $this->registry;
    }
}
