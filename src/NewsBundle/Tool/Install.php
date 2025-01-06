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

namespace NewsBundle\Tool;

use NewsBundle\Configuration\Configuration;
use NewsBundle\NewsBundle;
use Pimcore\Bundle\StaticRoutesBundle\Model\Staticroute;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\DataObject;
use Pimcore\Model\Translation;
use Pimcore\Model\User;
use Pimcore\Security\User\TokenStorageUserResolver;
use Pimcore\Tool;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class Install extends SettingsStoreAwareInstaller
{
    protected TokenStorageUserResolver $resolver;
    private SerializerInterface $serializer;
    private string $installSourcesPath;
    private Filesystem $fileSystem;
    private array $classes = [
        'NewsEntry',
        'NewsCategory',
    ];
    private string $currentVersion;

    public function setTokenStorageUserResolver(TokenStorageUserResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function install(): void
    {
        $this->installSourcesPath = __DIR__ . '/../Resources/install';
        $this->fileSystem = new Filesystem();
        $this->currentVersion = \Composer\InstalledVersions::getVersion(NewsBundle::PACKAGE_NAME);

        $this->installStaticRoutes();
        $this->installOrUpdateConfigFile();
        $this->installClasses();
        $this->installTranslations();
        $this->createFolders();

        parent::install();
    }

    private function installOrUpdateConfigFile(): void
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH)) {
            $this->fileSystem->mkdir(Configuration::SYSTEM_CONFIG_DIR_PATH);
        }

        $config = ['version' => $this->currentVersion];
        $yml = Yaml::dump($config);
        file_put_contents(Configuration::SYSTEM_CONFIG_FILE_PATH, $yml);
    }

    public function installClasses(): void
    {
        foreach ($this->getClasses() as $className => $path) {
            $class = new DataObject\ClassDefinition();

            try {
                $id = $class->getDao()->getIdByName($className);
            } catch (\Pimcore\Model\Exception\NotFoundException $e) {
                $id = false;
            }

            if ($id !== false) {
                continue;
            }

            $class->setName($className);

            $data = file_get_contents($path);
            DataObject\ClassDefinition\Service::importClassDefinitionFromJson($class, $data);
        }
    }

    public function installTranslations(): void
    {
        $csv = $this->installSourcesPath . '/translations/data.csv';
        Translation::importTranslationsFromFile($csv, Translation::DOMAIN_ADMIN, true, Tool\Admin::getLanguages());
    }

    public function createFolders(): void
    {
        $root = DataObject\Folder::getByPath('/news');
        $entries = DataObject\Folder::getByPath('/news/entries');
        $categories = DataObject\Folder::getByPath('/news/categories');

        if (!$root instanceof DataObject\Folder) {
            $root = DataObject\Folder::create([
                'parentId'         => 1,
                'creationDate'     => time(),
                'userOwner'        => $this->getUserId(),
                'userModification' => $this->getUserId(),
                'key'              => 'news',
                'published'        => true,
            ]);
        }

        if (!$entries instanceof DataObject\Folder) {
            DataObject\Folder::create([
                'parentId'         => $root->getId(),
                'creationDate'     => time(),
                'userOwner'        => $this->getUserId(),
                'userModification' => $this->getUserId(),
                'key'              => 'entries',
                'published'        => true,
            ]);
        }

        if (!$categories instanceof DataObject\Folder) {
            DataObject\Folder::create([
                'parentId'         => $root->getId(),
                'creationDate'     => time(),
                'userOwner'        => $this->getUserId(),
                'userModification' => $this->getUserId(),
                'key'              => 'categories',
                'published'        => true,
            ]);
        }
    }

    public function installStaticRoutes(): void
    {
        $conf = file_get_contents(__DIR__ . '/../Resources/install/staticroutes.json');
        $routes = $this->serializer->decode($conf, 'json');

        foreach ($routes['routes'] as $def) {
            if (!Staticroute::getByName($def['name'])) {
                $route = Staticroute::create();
                $route->setName($def['name']);
                $route->setPattern($def['pattern']);
                $route->setReverse($def['reverse']);
                $route->setController($def['controller']);
                $route->setVariables($def['variables']);
                $route->setPriority($def['priority']);
                $route->save();
            }
        }
    }

    protected function getClasses(): array
    {
        $result = [];

        foreach ($this->classes as $className) {
            $filename = sprintf('class_%s_export.json', $className);
            $path = dirname(__DIR__) . '/Resources/install/classes' . '/' . $filename;
            $path = realpath($path);

            if (false === $path || !is_file($path)) {
                throw new \RuntimeException(sprintf(
                    'Class export for class "%s" was expected in "%s" but file does not exist',
                    $className,
                    $path
                ));
            }

            $result[$className] = $path;
        }

        return $result;
    }

    protected function getUserId(): int
    {
        $userId = 0;
        $user = $this->resolver->getUser();
        if ($user instanceof User) {
            $userId = $this->resolver->getUser()->getId();
        }

        return $userId;
    }
}
