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

namespace NewsBundle\Configuration;

class Configuration
{
    public const SYSTEM_CONFIG_DIR_PATH = PIMCORE_PRIVATE_VAR . '/bundles/NewsBundle';
    public const SYSTEM_CONFIG_FILE_PATH = PIMCORE_PRIVATE_VAR . '/bundles/NewsBundle/config.yml';

    protected array $config;

    public function setConfig(array $config = []): void
    {
        $this->config = $config;
    }

    public function getConfigNode(): array
    {
        return $this->config;
    }

    public function getConfigArray(): array
    {
        return $this->config;
    }

    public function getConfig(string $slot): mixed
    {
        return $this->config[$slot];
    }
}
