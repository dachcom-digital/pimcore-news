<?php

namespace NewsBundle;

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
        return $this->container->get('news.tool.installer');
    }

    /**
     * @return string[]
     */
    public function getJsPaths()
    {
        return [
            '/bundles/news/js/startup.js',
            '/bundles/news/js/object/data/newsTypeSelect.js',
            '/bundles/news/js/object/tags/newsTypeSelect.js'
        ];
    }

}
