<?php

namespace NewsBundle\Event;

use Pimcore\Model\Document\Tag\Area\Info;
use Symfony\Component\EventDispatcher\Event;

class NewsBrickEvent extends Event
{
    /**
     * @var Info
     */
    protected $info;

    /**
     * @var array
     */
    protected $querySettings;

    /**
     * @var array
     */
    protected $additionalViewParams;

    /**
     * @param Info  $info
     * @param array $querySettings
     */
    public function __construct(Info $info, array $querySettings)
    {
        $this->info = $info;
        $this->querySettings = $querySettings;
    }

    /**
     * @return Info
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return array
     */
    public function getQuerySettings()
    {
        return $this->querySettings;
    }

    /**
     * @param array $querySettings
     */
    public function setQuerySettings(array $querySettings)
    {
        $this->querySettings = $querySettings;
    }

    /**
     * @return array
     */
    public function getAdditionalViewParams()
    {
        if (!is_array($this->additionalViewParams)) {
            return [];
        }

        return $this->additionalViewParams;
    }

    /**
     * @param array $additionalViewParams
     */
    public function addAdditionalViewParams(array $additionalViewParams)
    {
        if (!is_array($this->additionalViewParams)) {
            $this->additionalViewParams = [];
        }

        $this->additionalViewParams = array_merge($this->additionalViewParams, $additionalViewParams);
    }
}
