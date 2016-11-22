<?php

namespace News;

use Pimcore\API\Plugin as PluginLib;
use News\Plugin\Install;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    /**
     * @var \Zend_Translate
     */
    protected static $_translate;

    public function preDispatch($e) {

        $e->getTarget()->registerPlugin(new Controller\Plugin\Frontend());
    }

    public function init()
    {
        parent::init();

        \Pimcore::getEventManager()->attach(
            'object.postAdd',
            array('\\News\\Event\\SeoUrl', 'setObjectFrontendUrl')
        );
        \Pimcore::getEventManager()->attach(
            'object.postUpdate',
            array('\\News\\Event\\SeoUrl', 'setObjectFrontendUrl')
        );
    }

    /**
     * @return string
     */
    public static function install()
    {
        try
        {
            $install = new \News\Plugin\Install();
            $install->createConfig();
            $install->createStaticRoutes();
            $install->installAdminTranslations();
            $install->createFolders();
            $install->createClasses();
        }
        catch (Exception $e)
        {
            \Pimcore\Logger::crit($e);

            return self::getTranslate()->_('news_install_failed');
        }

        return self::getTranslate()->_('news_installed_successfully');
    }

    /**
     * @return string
     */
    public static function uninstall()
    {
        try
        {
            $install = new \News\Plugin\Install();
            $install->removeConfig();
            $install->removeStaticRoutes();

            return self::getTranslate()->_('news_uninstalled_successfully');
        }
        catch (Exception $e)
        {
            \Pimcore\Logger::crit($e);
            return self::getTranslate()->_('news_uninstall_failed');
        }
    }

    /**
     * @return bool
     */
    public static function isInstalled()
    {
        $install = new Install();
        return $install->isInstalled();
    }

    /**
     * @return \Zend_Translate
     */
    public static function getTranslate()
    {
        if (self::$_translate instanceof \Zend_Translate)
        {
            return self::$_translate;
        }

        try
        {
            $lang = \Zend_Registry::get('Zend_Locale')->getLanguage();
        }
        catch (Exception $e)
        {
            $lang = 'en';
        }

        self::$_translate = new \Zend_Translate('csv', PIMCORE_PLUGINS_PATH . self::getTranslationFile($lang), $lang, array('delimiter' => ','));

        return self::$_translate;
    }
}
