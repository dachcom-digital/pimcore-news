<?php

namespace News\Controller\Widget;

abstract class AbstractWidget
{
    /**
     * @var string
     */
    private $widgetName = '';

    /**
     * @var array
     */
    protected $areaSettings = [];

    /**
     * @var \Pimcore\View
     */
    protected $view;

    /**
     * AbstractWidget constructor.
     *
     * @param string $widgetName
     * @param array  $areaSettings
     */
    public function __construct($widgetName = '', $areaSettings = [])
    {
        $this->widgetName = $widgetName;
        $this->areaSettings = $areaSettings;
    }

    /**
     * @param \Pimcore\View $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return void
     */
    public function action()
    {
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {
        if (substr($method, 0, 3) == 'get') {
            $varName = lcfirst(substr($method, 3));
        } else {
            throw new \Exception('Bad method.', 500);
        }
        if (array_key_exists($varName, $this->areaSettings)) {
            return $this->areaSettings[$varName];
        } else {
            throw new \Exception('Property does not exist: ' . $varName, 500);
        }
    }

}