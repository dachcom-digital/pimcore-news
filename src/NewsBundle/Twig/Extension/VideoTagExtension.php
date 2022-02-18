<?php

namespace NewsBundle\Twig\Extension;

use Pimcore\Model\Asset;
use Pimcore\Model\Document\Editable\Video;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VideoTagExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('news_video_tag', [$this, 'generateVideoTag'], ['is_safe' => ['html']])
        ];
    }

    public function generateVideoTag(Video $videoType, array $customOptions = []): string
    {
        $html = '';
        $videoData = $videoType->getData();

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

        $video->setTitle($videoType->getTitle());
        $video->setDescription($videoType->getDescription());

        $data = [
            'id'          => $videoData instanceof Asset ? $videoData->getId() : $videoData,
            'type'        => $videoType->getType(),
            'title'       => $videoType->getTitle(),
            'description' => $videoType->getDescription(),
        ];

        if ($videoType->getPoster()) {
            $data['poster'] = $videoType->getPoster();
        }

        $video->setDataFromResource(\Pimcore\Tool\Serialize::serialize($data));

        return $video->frontend();
    }

}