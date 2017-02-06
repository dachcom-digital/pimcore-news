<?php

namespace News\Model;

use Pimcore\Model;
use Pimcore\Tool;

class LocalizedFields extends Model\AbstractModel
{
    const STRICT_DISABLED = 0;


    const STRICT_ENABLED = 1;

    /**
     * @var bool
     */
    private static $getFallbackValues = FALSE;

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var AbstractModel
     */
    public $object;

    /**
     * @var bool
     */
    private static $strictMode;

    /**
     * @param boolean $getFallbackValues
     */
    public static function setGetFallbackValues($getFallbackValues)
    {
        self::$getFallbackValues = $getFallbackValues;
    }

    /**
     * @return boolean
     */
    public static function getGetFallbackValues()
    {
        return self::$getFallbackValues;
    }

    /**
     * @return boolean
     */
    public static function isStrictMode()
    {
        return self::$strictMode;
    }

    /**
     * @param boolean $strictMode
     */
    public static function setStrictMode($strictMode)
    {
        self::$strictMode = $strictMode;
    }

    /**
     * @return boolean
     */
    public static function doGetFallbackValues()
    {
        return self::$getFallbackValues;
    }

    /**
     * LocalizedFields constructor.
     *
     * @param      $fields
     * @param null $items
     */
    public function __construct($fields, $items = NULL)
    {
        if ($items) {
            $this->setItems($items);
        }

        $this->setFields($fields);
    }

    /**
     * @param  $item
     *
     * @return void
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * @param  array $items
     *
     * @return void
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $fields
     *
     * @return void
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param AbstractModel $object
     *
     * @return void
     */
    public function setObject(AbstractModel $object)
    {
        $this->object = $object;
    }

    /**
     * @return AbstractModel
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @throws \Exception
     *
     * @param null $language
     *
     * @return string
     */
    public function getLanguage($language = NULL)
    {
        if ($language) {
            return (string)$language;
        }

        // try to get the language from the registry
        try {
            $locale = \Zend_Registry::get("Zend_Locale");
            if (Tool::isValidLanguage((string)$locale)) {
                return (string)$locale;
            }
            throw new \Exception("Not supported language");
        } catch (\Exception $e) {
            return Tool::getDefaultLanguage();
        }
    }

    /**
     * @param $language
     *
     * @return bool
     */
    public function languageExists($language)
    {
        return array_key_exists($language, $this->getItems());
    }

    /**
     * @param      $name
     * @param null $language
     * @param bool $ignoreFallbackLanguage
     *
     * @return null
     */
    public function getLocalizedValue($name, $language = NULL, $ignoreFallbackLanguage = FALSE)
    {
        $data = NULL;
        $language = $this->getLanguage($language);

        if ($this->languageExists($language)) {
            if (array_key_exists($name, $this->items[$language])) {
                $data = $this->items[$language][$name];
            }
        }

        // check for fallback value
        if (!$data && !$ignoreFallbackLanguage && self::doGetFallbackValues()) {
            foreach (Tool::getFallbackLanguagesFor($language) as $l) {
                if ($this->languageExists($l)) {
                    if (array_key_exists($name, $this->items[$l])) {
                        $data = $this->getLocalizedValue($name, $l);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param      $name
     * @param      $value
     * @param null $language
     *
     * @return void
     * @throws \Exception
     */
    public function setLocalizedValue($name, $value, $language = NULL)
    {
        if (self::$strictMode) {
            if (!$language || !in_array($language, Tool::getValidLanguages())) {
                throw new \Exception("Language " . $language . " not accepted in strict mode");
            }
        }

        $language = $this->getLanguage($language);
        if (!$this->languageExists($language)) {
            $this->items[$language] = [];
        }

        $this->items[$language][$name] = $value;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ["items"];
    }
}
