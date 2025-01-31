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

namespace NewsBundle\Twig\Extension;

use Pimcore\Model\Asset;
use Pimcore\Model\Document\Editable\Video;
use Pimcore\Tool\Serialize;
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

    public function generateVideoTag(\Pimcore\Model\DataObject\Data\Video $videoType, array $customOptions = []): string
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

        $video->setConfig($videoOptions);
        $video->setTitle($videoType->getTitle());
        $video->setDescription($videoType->getDescription());

        $data = [
            'id'          => $videoData instanceof Asset ? $videoData->getId() : $videoData,
            'type'        => $videoType->getType(),
            'title'       => $videoType->getTitle(),
            'description' => $videoType->getDescription(),
            'poster'      => null,
        ];

        if ($videoType->getPoster() instanceof Asset) {
            $data['poster'] = $videoType->getPoster()->getId();
        }

        $video->setDataFromResource(Serialize::serialize($data));

        return $video->frontend();
    }
}
