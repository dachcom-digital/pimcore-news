<?php

namespace News\Plugin;

use News\Model\Configuration;

use Pimcore\Model\Object;
use Pimcore\Model\Object\Folder;
use Pimcore\Model\Staticroute;
use Pimcore\Model\Translation\Admin;
use Pimcore\Model\User;
use Pimcore\Tool;

class Install {

    /**
     * @var User
     */
    protected $_user;

    public function isInstalled()
    {
        $configFile = \Pimcore\Config::locateConfigFile('news_configurations');

        if (is_file($configFile . '.php'))
        {
            $isInstalled = Configuration::get('news_is_installed');

            if ($isInstalled)
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function createConfig()
    {
        $configFile = \Pimcore\Config::locateConfigFile('news_configurations');

        if (is_file($configFile . '.BACKUP'))
        {
            rename($configFile . '.BACKUP', $configFile . '.php');
            return TRUE;
        }

        Configuration::set('news_list_settings', [
            'sortby' => 'date',
            'orderby' => 'desc',
            'maxItems' => 0,
            'paginate' => [
                'itemsPerPage' => 10
            ]
        ]);

        Configuration::set('news_detail_settings', [
            'your_page_property' => [
                'type' => 'bool',
                'data' => 0
            ]
        ]);

        Configuration::set('news_is_installed', TRUE);

        return TRUE;

    }

    public function removeConfig()
    {
        $configFile = \Pimcore\Config::locateConfigFile('news_configurations');

        if (is_file($configFile . '.php'))
        {
            rename($configFile . '.php', $configFile . '.BACKUP');
        }
    }

    public function installAdminTranslations()
    {
        $csv = PIMCORE_PLUGINS_PATH . '/News/install/translations/data.csv';
        Admin::importTranslationsFromFile($csv, true, \Pimcore\Tool\Admin::getLanguages());
    }

    /**
     * Creates News Static Routes
     */
    public function createStaticRoutes()
    {
        $conf = new \Zend_Config_Xml(PIMCORE_PLUGINS_PATH . '/News/install/staticroutes.xml');

        foreach ($conf->routes as $def) {

            if (!Staticroute::getByName($def->name))
            {
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
    public function removeStaticRoutes()
    {
        $conf = new \Zend_Config_Xml(PIMCORE_PLUGINS_PATH . '/News/install/staticroutes.xml');

        foreach ($conf->routes as $routeData)
        {
            $route = Staticroute::getByName($routeData->name);

            if ($route)
            {
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
    protected function createClass($className, $updateClass = false)
    {
        $class = Object\ClassDefinition::getByName($className);

        if (!$class || $updateClass)
        {
            $jsonFile = PIMCORE_PLUGINS_PATH . "/News/install/object/structures/$className.json";
            $json = file_get_contents($jsonFile);

            if (!$class)
            {
                $class = Object\ClassDefinition::create();
            }

            $class->setName($className);
            $class->setUserOwner($this->_getUserId());

            Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, true);

            return $class;
        }

        return $class;
    }

    public function createClasses()
    {
        $this->createClass('NewsCategory');
        $this->createClass('NewsEntry');
    }

    /**
     * Create needed News Folders
     * @return Object\AbstractObject|Folder
     */
    public function createFolders()
    {
        $root = Folder::getByPath('/news');
        $entries = Folder::getByPath('/news/entries');
        $categories = Folder::getByPath('/news/categories');

        if (!$root instanceof Folder)
        {
            $root = Folder::create([
                'o_parentId'         => 1,
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUserId(),
                'o_userModification' => $this->_getUserId(),
                'o_key'              => 'news',
                'o_published'        => true,
            ]);
        }

        if (!$entries instanceof Folder)
        {
            Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUserId(),
                'o_userModification' => $this->_getUserId(),
                'o_key'              => 'entries',
                'o_published'        => true,
            ]);
        }

        if (!$categories instanceof Folder)
        {
            Folder::create([
                'o_parentId'         => $root->getId(),
                'o_creationDate'     => time(),
                'o_userOwner'        => $this->_getUserId(),
                'o_userModification' => $this->_getUserId(),
                'o_key'              => 'categories',
                'o_published'        => true,
            ]);
        }

        return TRUE;
    }

    /**
     * @return \Int User Id
     */
    protected function _getUserId()
    {
        $userId = 0;
        $user = Tool\Admin::getCurrentUser();

        if ($user)
        {
            $userId = $user->getId();
        }

        return $userId;
    }
}
