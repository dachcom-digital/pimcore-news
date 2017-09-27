<?php

namespace NewsBundle;

use NewsBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class NewsBundle extends AbstractPimcoreBundle
{
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
