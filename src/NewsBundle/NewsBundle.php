<?php

namespace NewsBundle;

use NewsBundle\DependencyInjection\CompilerPass\PresetPass;
use NewsBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class NewsBundle extends AbstractPimcoreBundle
{
    const BUNDLE_VERSION = '2.0.4';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new PresetPass());
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
    public function getEditmodeJsPaths()
    {
        return [
            '/bundles/news/js/admin/area.js'
        ];
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

    /**
     * @return string[]
     */
    public function getEditmodeCssPaths()
    {
        return [
            '/bundles/news/css/admin-editmode.css',
        ];
    }

}
