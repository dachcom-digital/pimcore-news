<?php

namespace News\Plugin;

use News\Model\Configuration;
use Pimcore\Model\Object;
use Pimcore\Model\Object\Folder;
use Pimcore\Model\Staticroute;
use Pimcore\Model\User;

class Install {

    /**
     * @var User
     */
    protected $_user;

    /**
     * @var \Zend_EventManager_EventManager
     */
    private static $eventManager;

    public function isInstalled() {

        $configFile = \Pimcore\Config::locateConfigFile('news_configurations');

        if (is_file($configFile . '.php')) {

            $isInstalled = Configuration::get("news_is_installed");

            if ($isInstalled) return true;

        }

        return false;
    }

    public function createConfig() {

        Configuration::set("news_latest_settings", [
            'maxItems' => 3
        ]);

        Configuration::set("news_list_settings", [
            'maxItems' => 0,
            'paginate' => [
                'itemsPerPage' => 10
            ]
        ]);

        Configuration::set("news_detail_page", [
            'de' => 1
        ]);

        Configuration::set("news_is_installed", true);
    }

    public function removeConfig() {

        $configFile = \Pimcore\Config::locateConfigFile('news_configurations');

        if (is_file($configFile . '.php')) {
            rename($configFile . '.php', $configFile . '.BACKUP');
        }
    }

    /**
     * Creates News Static Routes
     */
    public function createStaticRoutes() {

        $conf = new \Zend_Config_Xml(PIMCORE_PLUGINS_PATH . '/News/install/staticroutes.xml');

        foreach ($conf->routes as $def) {
            if (!Staticroute::getByName($def->name)) {
                $route = Staticroute::create();
                $route->setName($def->name);
                $route->setPattern($def->pattern);
                $route->setReverse($def->reverse);
                $route->setModule($def->module);
                $route->setController($def->controller);
                $route->setAction($def->action);
                $route->setVariables($def->variables);
                $route->setPriority($def->priority);
                $route->save();
            }
        }
    }

    /**
     * Remove News Static Routes
     */
    public function removeStaticRoutes() {
        $conf = new \Zend_Config_Xml(PIMCORE_PLUGINS_PATH . '/News/install/staticroutes.xml');

        foreach ($conf->routes->route as $def) {
            $route = Staticroute::getByName($def->name);
            if ($route) {
                $route->delete();
            }
        }
    }

    /**
     * creates a mew Class if it doesn't exists
     *
     * @param      $className
     * @param bool $updateClass should class be updated if it already exists
     *
     * @return mixed|Object\ClassDefinition
     */
    protected function createClass($className, $updateClass = false) {
        $class = Object\ClassDefinition::getByName($className);

        if (!$class || $updateClass) {
            $jsonFile = PIMCORE_PLUGINS_PATH . "/News/install/object/structures/$className.json";
            $json = file_get_contents($jsonFile);

            if (!$class) {
                $class = Object\ClassDefinition::create();
            }

            $class->setName($className);
            $class->setUserOwner($this->_getUser()->getId());

            Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, true);

            return $class;
        }

        return $class;
    }

    public function createClasses() {

        $this->createClass("NewsCategory");
        $this->createClass("NewsEntry");
    }

    public function removeClasses() {

         $this->removeClass("NewsCategory");
         $this->removeClass("NewsEntry");
    }

    /**
     * Removes a class definition
     *
     * @param $name
     */
    protected function removeClass($name) {
        $class = Object\ClassDefinition::getByName($name);

        if ($class) {
            $class->delete();
        }
    }

    /**
     * Create needed News Folders
     * @return Object\AbstractObject|Folder
     */
    public function createFolders() {
        $root = Folder::getByPath("/news");
        $entries = Folder::getByPath("/news/entries");
        $categories = Folder::getByPath("/news/categories");

        if (!$root instanceof Folder) {
            $root = Folder::create([
                'o_parentId'         => 1,
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUser()->getId(),
                'o_userModification' => $this->_getUser()->getId(),
                'o_key'              => 'news',
                'o_published'        => true,
            ]);
        }

        if (!$entries instanceof Folder) {
            Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUser()->getId(),
                'o_userModification' => $this->_getUser()->getId(),
                'o_key'              => 'entries',
                'o_published'        => true,
            ]);
        }

        if (!$categories instanceof Folder) {
            Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUser()->getId(),
                'o_userModification' => $this->_getUser()->getId(),
                'o_key'              => 'categories',
                'o_published'        => true,
            ]);
        }

        return $root;
    }

    /**
     * Remove News Folders
     */
    public function removeFolders() {
         $blogFolder = Folder::getByPath('/news');
         if ($blogFolder) {
             $blogFolder->delete();
        }
    }

    /**
     * @return User
     */
    protected function _getUser() {
        if (!$this->_user) {
            $this->_user = \Zend_Registry::get('pimcore_admin_user');
        }

        return $this->_user;
    }

    /**
     * @return \Zend_EventManager_EventManager
     */
    public static function getEventManager() {
        if (!self::$eventManager) {
            self::$eventManager = new \Zend_EventManager_EventManager();
        }

        return self::$eventManager;
    }
}
