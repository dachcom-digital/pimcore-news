<?php

namespace NewsBundle\Twig\Extension;

use Pimcore\Model\Asset;
use Pimcore\Model\Document\Tag\Video;

class VideoTagExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('news_video_tag', [$this, 'generateVideoTag'], [
                'is_safe' => ['html']
            ])
        ];
    }

    /**
     * @param \Pimcore\Model\DataObject\Data\Video $videoType
     * @param array                            $customOptions
     *
     * @return string
     */
    public function generateVideoTag($videoType, $customOptions = [])
    {
        $videoData = $videoType->getData();

        $html = '';

        if (!$videoData) {
            return $html;
        }

        $video = new Video();

        $videoOptions = array_merge([
            'thumbnail'  => 'content',
            'width'      => '100%',
            'height'     => 'auto',
            'attributes' => [
                'class'    => 'video-js',
                'preload'  => 'auto',
                'controls' => ''
            ]
        ], $customOptions);

        $video->setOptions($videoOptions);

        $video->type = $videoType->getType();
        $video->id = ($videoData instanceof Asset) ? $videoData->getId() : $videoData;
        $video->title = $videoType->getTitle();
        $video->description = $videoType->getDescription();

        if ($videoType->getPoster()) {
            $video->poster = $videoType->getPoster()->getId();
        }

        $html = $video->frontend();

        return $html;
    }

}