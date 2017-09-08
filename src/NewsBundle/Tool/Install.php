<?php

namespace NewsBundle\Tool;

use Pimcore\Cache\Runtime;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;

use Pimcore\Model\Staticroute;
use Pimcore\Tool;
use Pimcore\Model\DataObject;
use Pimcore\Model\Translation;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use NewsBundle\Configuration\Configuration;

class Install extends AbstractInstaller
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $installSourcesPath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var array
     */
    private $classes = [
        'NewsEntry',
        'NewsCategory',
    ];

    /**
     * @var User
     */
    protected $_user;

    /**
     * Install constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        parent::__construct();

        $this->serializer = $serializer;
        $this->installSourcesPath = __DIR__ . '/../Resources/install';
        $this->fileSystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->installStaticRoutes();
        $this->copyConfigFiles();
        $this->installClasses();
        $this->installTranslations();
        $this->createFolders();

        return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $this->fileSystem->rename(
                Configuration::SYSTEM_CONFIG_FILE_PATH,
                PIMCORE_PRIVATE_VAR . '/bundles/NewsBundle/config_backup.yml'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled()
    {
        return !$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUninstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return FALSE;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUpdated()
    {
        return FALSE;
    }

    /**
     * copy sample config file - if not exists.
     */
    private function copyConfigFiles()
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $this->fileSystem->copy(
                $this->installSourcesPath . '/config.yml',
                Configuration::SYSTEM_CONFIG_FILE_PATH
            );
        }

    }

    /**
     * @return bool
     */
    public function installClasses()
    {
        foreach ($this->getClasses() as $className => $path) {

            $class = new DataObject\ClassDefinition();
            $id = $class->getDao()->getIdByName($className);

            if ($id !== FALSE) {
                continue;

            }

            $class->setName($className);

            $data = file_get_contents($path);
            $success = DataObject\ClassDefinition\Service::importClassDefinitionFromJson($class, $data);

        }
    }

    /**
     *
     */
    public function installTranslations()
    {
        $csv = $this->installSourcesPath . '/translations/data.csv';
        Translation\Admin::importTranslationsFromFile($csv, TRUE, Tool\Admin::getLanguages());
    }

    /**
     * @return bool
     */
    public function createFolders()
    {
        $root = DataObject\Folder::getByPath('/news');
        $entries = DataObject\Folder::getByPath('/news/entries');
        $categories = DataObject\Folder::getByPath('/news/categories');

        if (!$root instanceof DataObject\Folder) {
            $root = DataObject\Folder::create([
                'o_parentId'         => 1,
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUser(),
                'o_userModification' => $this->_getUser(),
                'o_key'              => 'news',
                'o_published'        => TRUE,
            ]);
        }

        if (!$entries instanceof DataObject\Folder) {
            DataObject\Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUser(),
                'o_userModification' => $this->_getUser(),
                'o_key'              => 'entries',
                'o_published'        => TRUE,
            ]);
        }

        if (!$categories instanceof DataObject\Folder) {
            DataObject\Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUser(),
                'o_userModification' => $this->_getUser(),
                'o_key'              => 'categories',
                'o_published'        => TRUE,
            ]);
        }

        return TRUE;

    }

    /**
     * Creates News Static Routes
     */
    public function installStaticRoutes()
    {
        $conf = file_get_contents(dirname(__FILE__) . '/../Resources/install/staticroutes.json');
        $routes = $this->serializer->decode($conf, 'json');

        foreach ($routes['routes'] as $def) {
            if (!Staticroute::getByName($def['name'])) {
                $route = Staticroute::create();
                $route->setName($def['name']);
                $route->setPattern($def['pattern']);
                $route->setReverse($def['reverse']);
                $route->setModule($def['module']);
                $route->setController($def['controller']);
                $route->setAction($def['action']);
                $route->setVariables($def['variables']);
                $route->setPriority($def['priority']);
                $route->save();
            }
        }
    }

    /**
     * @return array
     */
    protected function getClasses(): array
    {
        $result = [];

        foreach ($this->classes as $className) {
            $filename = sprintf('class_%s_export.json', $className);
            $path = realpath(dirname(__FILE__) . '/../Resources/install/classes') . '/' . $filename;
            $path = realpath($path);

            if (FALSE === $path || !is_file($path)) {
                throw new \RuntimeException(sprintf(
                    'Class export for class "%s" was expected in "%s" but file does not exist',
                    $className, $path
                ));
            }

            $result[$className] = $path;
        }

        return $result;
    }

    /**
     * @return User
     */
    protected function _getUser()
    {
        if (!$this->_user) {
            $this->_user = Runtime::get('pimcore_admin_user');
        }

        return $this->_user;
    }

}