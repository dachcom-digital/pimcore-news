<?php

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