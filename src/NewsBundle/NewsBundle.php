<?php

namespace NewsBundle;

use NewsBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class NewsBundle extends AbstractPimcoreBundle
{
    const BUNDLE_VERSION = '2.0.2';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return self::BUNDLE_VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return $this->container->get(Install::class);
    }

    /**
     * @return string[]
     */
    public function getJsPaths()
    {
        return [
            '/bundles/news/js/startup.js',
            '/bundles/news/js/object/data/entryTypeSelect.js',
            '/bundles/news/js/object/tags/entryTypeSelect.js'
        ];
    }

}
