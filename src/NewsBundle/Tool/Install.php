<?php

namespace NewsBundle\Tool;

use PackageVersions\Versions;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Tool;
use Pimcore\Model\User;
use Pimcore\Model\DataObject;
use Pimcore\Model\Translation;
use Pimcore\Model\Staticroute;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Symfony\Component\Filesystem\Filesystem;
use NewsBundle\NewsBundle;
use NewsBundle\Configuration\Configuration;
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
        if (class_exists(Versions::class)) {
            $this->currentVersion = Versions::getVersion(NewsBundle::PACKAGE_NAME);
        } else {
            $this->currentVersion = '';
        }

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
                'o_parentId'         => 1,
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->getUserId(),
                'o_userModification' => $this->getUserId(),
                'o_key'              => 'news',
                'o_published'        => true,
            ]);
        }

        if (!$entries instanceof DataObject\Folder) {
            DataObject\Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->getUserId(),
                'o_userModification' => $this->getUserId(),
                'o_key'              => 'entries',
                'o_published'        => true,
            ]);
        }

        if (!$categories instanceof DataObject\Folder) {
            DataObject\Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->getUserId(),
                'o_userModification' => $this->getUserId(),
                'o_key'              => 'categories',
                'o_published'        => true,
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
                    $className, $path
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
