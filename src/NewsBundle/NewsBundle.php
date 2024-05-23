<?php

namespace NewsBundle;

use NewsBundle\DependencyInjection\CompilerPass\PresetPass;
use NewsBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NewsBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'dachcom-digital/news';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new PresetPass());
    }

    public function getInstaller(): Install
    {
        return $this->container->get(Install::class);
    }

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/news/js/startup.js',
            '/bundles/news/js/object/data/entryTypeSelect.js',
            '/bundles/news/js/object/tags/entryTypeSelect.js'
        ];
    }


    public function getCssPaths(): array
    {
        return [];
    }

    public function getEditmodeJsPaths(): array
    {
        return [];
    }

    public function getEditmodeCssPaths(): array
    {
        return [];
    }
}
