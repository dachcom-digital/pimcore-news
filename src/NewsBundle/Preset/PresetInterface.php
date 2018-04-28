<?php

namespace NewsBundle\Preset;

use Pimcore\Model\Document\Tag\Area\Info;

interface PresetInterface
{
    /**
     * Returns name for drop down selection.
     * This value gets applied via translation engine
     * so use a good translation string like "news.preset.my_preset_name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns a description for drop down selection.
     * This value gets applied via translation engine
     * so use a good translation string like "news.preset.my_preset_description
     * Return NULL if you don't want to provide any description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Every preset comes with the Area Info.
     *
     * @param Info $info
     */
    public function setInfo(Info $info);

    /**
     * This method needs to return a key value array.
     *
     * @return array
     */
    public function getViewParams(): array;

}