<?php

namespace NewsBundle;

use NewsBundle\DependencyInjection\CompilerPass\PresetPass;
use NewsBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NewsEvents
{
   const NEWS_BRICK_QUERY_BUILD = 'news.brick.query_build';
}
