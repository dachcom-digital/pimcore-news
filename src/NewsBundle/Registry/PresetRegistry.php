<?php

namespace NewsBundle\Registry;

use NewsBundle\Preset\PresetInterface;

class PresetRegistry
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * {@inheritdoc}
     */
    public function register($alias, $service)
    {
        if (!in_array(PresetInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), PresetInterface::class,
                    implode(', ', class_implements($service)))
            );
        }

        $this->registry[$alias] = $service;
    }

    /**
     * @param $alias
     *
     * @return bool
     */
    public function has($alias)
    {
        return isset($this->registry[$alias]);
    }

    /**
     * @param $alias
     *
     * @return PresetInterface
     * @throws \Exception
     */
    public function get($alias)
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
