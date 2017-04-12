<?php

namespace News\Controller;

use Pimcore\View;

class WidgetHandler
{
    /**
     * @var array
     */
    protected $areaSettings = [];

    /**
     * @var array
     */
    private $helperPaths = [];

    /**
     * @var bool
     */
    private $editMode = FALSE;

    /**
     * @var array
     */
    private $widgetCache = [];

    /**
     * WidgetHandler constructor.
     *
     * @param $areaSettings
     */
    public function __construct($areaSettings)
    {
        $this->areaSettings = $areaSettings;
    }

    /**
     * @param $widgetName
     *
     * @return mixed|string
     */
    public function getWidget($widgetName)
    {
        //check cache data
        if (isset($this->widgetCache[$widgetName])) {
            return $this->widgetCache[$widgetName];
        }

        $newsPath = PIMCORE_PLUGINS_PATH . '/News/lib/News/Controller/Widget/' . ucfirst($widgetName) . '.php';
        $modulePath = PIMCORE_WEBSITE_PATH . '/lib/Website/Controller/News/Widget/' . ucfirst($widgetName) . '.php';

        if (is_file($newsPath)) {
            $className = '\\News\\Controller\\Widget\\' . ucfirst($widgetName);
        } else if (is_file($modulePath)) {
            $className = '\\Website\\Controller\\News\\Widget\\' . ucfirst($widgetName);
        }

        /** @var \News\Controller\Widget\AbstractWidget $widget */
        $widget = new $className($widgetName, $this->areaSettings);

        $view = new View();
        foreach ($this->helperPaths as $ns => $path) {
            $view->addHelperPath($path, $ns);
        }

        $view->setScriptPath(
            [
                PIMCORE_PLUGINS_PATH . '/News/views/scripts/news/widget/' . lcfirst($widgetName),
                PIMCORE_WEBSITE_PATH . '/views/scripts/news/widget/' . lcfirst($widgetName)
            ]
        );

        $view->assign('editmode', $this->editMode);

        $widget->setView($view);
        $widget->action();

        //capture widget html
        $html = $view->template('view.php', [], TRUE, 'news_widget_' . strtolower($widgetName));

        //add data to cache.
        $this->widgetCache[$widgetName] = $html;

        return $html;
    }

    /**
     * @param $editMode
     */
    public function setEditMode($editMode = FALSE)
    {
        $this->editMode = $editMode;
    }

    /**
     * @param array $paths
     */
    public function passHelperPaths($paths = [])
    {
        $this->helperPaths = $paths;
    }
}