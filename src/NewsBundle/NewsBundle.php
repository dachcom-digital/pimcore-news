<?php

namespace NewsBundle;

use NewsBundle\DependencyInjection\CompilerPass\PresetPass;
use NewsBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NewsBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;
    const PACKAGE_NAME = 'dachcom-digital/news';

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

    /**
     * @inheritDoc
     */
    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
