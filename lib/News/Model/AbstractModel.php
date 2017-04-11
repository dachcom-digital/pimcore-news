<?php

namespace News\Model;

use Pimcore\Cache;
use Pimcore\Model;
use Pimcore\Tool;

class AbstractModel extends Model\AbstractModel
{
    /**
     * @var array
     */
    protected $localizedValues = [];

    /**
     * @var LocalizedFields
     */
    protected $localizedFields;

    /**
     * Get Range by id
     *
     * @param $id
     *
     * @return null|AbstractModel
     */
    public static function getById($id)
    {
        $id = intval($id);

        if ($id < 1) {
            return NULL;
        }

        $className = get_called_class();
        $cacheKey = self::getCacheKey($className, $id);

        try {
            $object = \Zend_Registry::get($cacheKey);
            if (!$object) {
                throw new \Exception($className . ' in registry is null');
            }

            return $object;
        } catch (\Exception $e) {
            try {
                if (!$object = Cache::load($cacheKey)) {
                    $className = Tool::getModelClassMapping($className);

                    $object = new $className();
                    $object->getDao()->getById($id);

                    \Zend_Registry::set($cacheKey, $object);
                    Cache::save($object, $cacheKey);
                } else {
                    \Zend_Registry::set($cacheKey, $object);
                }

                return $object;
            } catch (\Exception $e) {
                \Pimcore\Logger::warning($e->getMessage());
            }
        }

        return NULL;
    }

    /**
     * Get Range by id
     *
     * @param string $field
     * @param string $value
     *
     * @return null|AbstractModel
     */
    public static function getByField($field, $value)
    {
        //Todo: what if a object changes and is still in cache?

        $className = get_called_class();
        $cacheKey = self::getCacheKey($className, $field . "_" . $value);

        try {
            $object = \Zend_Registry::get($cacheKey);
            if (!$object) {
                throw new \Exception($className . " in registry is null");
            }

            return $object;
        } catch (\Exception $e) {
            try {
                if (!$object = Cache::load($cacheKey)) {
                    $object = new $className();
                    $object->getDao()->getByField($field, $value);

                    \Zend_Registry::set($cacheKey, $object);
                    Cache::save($object, $cacheKey);
                } else {
                    \Zend_Registry::set($cacheKey, $object);
                }

                return $object;
            } catch (\Exception $e) {
                \Pimcore\Logger::warning($e->getMessage());
            }
        }

        return NULL;
    }

    /**
     * @param $className
     *
     * @return string
     */
    protected static function getCacheKey($className, $append)
    {
        return 'news_' . str_replace("\\", '_', $className) . '_' . $append;
    }

    /**
     *
     */
    public function save()
    {
        $this->getDao()->save();

        $cacheKey = self::getCacheKey(get_called_class(), $this->getId());

        //unset object in cache
        Cache::clearTag($cacheKey);
        \Zend_Registry::set($cacheKey, NULL);
    }

    /**
     * Get LocalizedFields Provider
     * @return LocalizedFields|null
     */
    public function getLocalizedFields()
    {
        if (count($this->localizedValues) > 0) {
            if (is_null($this->localizedFields)) {
                $this->localizedFields = new LocalizedFields($this->localizedValues);
                $this->localizedFields->setObject($this);
            }

            return $this->localizedFields;
        }

        return NULL;
    }

    /**
     * Get LocalizedFields Provider
     * @return LocalizedFields|null
     */
    public function setLocalizedFields($localizedFields)
    {
        $this->localizedFields = $localizedFields;
    }

    /**
     * Override setValue function to support localized fields
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setValue($key, $value)
    {
        if ($this->getLocalizedFields()) {
            $mykey = explode('.', $key); //0 => key, 1 => language

            if (in_array($mykey [0], $this->localizedValues)) {
                $this->getLocalizedFields()->setLocalizedValue($mykey [0], $value, $mykey [1]);
                return $this;
            }
        }

        return parent::setValue($key, $value);
    }

    public function __wakeup()
    {
        if ($this->getLocalizedFields()) {
            $this->getLocalizedFields()->setObject($this);
        }
    }
}
