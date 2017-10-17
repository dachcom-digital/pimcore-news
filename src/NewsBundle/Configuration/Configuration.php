<?php

namespace NewsBundle\Configuration;

class Configuration
{
    const SYSTEM_CONFIG_DIR_PATH = PIMCORE_PRIVATE_VAR . '/bundles/NewsBundle';

    const SYSTEM_CONFIG_FILE_PATH = PIMCORE_PRIVATE_VAR . '/bundles/NewsBundle/config.yml';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $systemConfig;

    /**
     * @param array $config
     */
    public function setConfig($config = [])
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfigNode()
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getConfigArray()
    {
        return $this->config;
    }

    /**
     * @param $slot
     *
     * @return mixed
     */
    public function getConfig($slot)
    {
        return $this->config[$slot];
    }
}