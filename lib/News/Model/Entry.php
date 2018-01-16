<?php

namespace News\Model;

use Pimcore\Model\Object;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\NewsCategory;
use Pimcore\Tool;

class Entry extends Concrete
{
    /**
     * Admin Element Style.
     *
     * @return \Pimcore\Model\Element\AdminStyle
     */
    public function getElementAdminStyle()
    {
        if (empty($this->o_elementAdminStyle)) {
            $class = '\\News\\Model\\AdminStyle';
            if (Tool::classExists($class)) {
                $this->o_elementAdminStyle = new AdminStyle($this);
            } else {
                return parent::getElementAdminStyle();
            }
        }

        return $this->o_elementAdminStyle;
    }

    /**
     * Get all News
     *
     * @return array
     */
    public static function getAll()
    {
        $newsListing = Object\NewsEntry::getList();
        static::modifyListing($newsListing);

        return $newsListing->getObjects();
    }

    /**
     * Get News from the Category with Paging
     *
     * @param array $params
     *
     * @return \Zend_Paginator
     */
    public static function getEntriesPaging(array $params = [])
    {
        $settings = array_merge([
            'sort'                 => [
                'field' => 'date',
                'dir'   => 'desc'
            ],
            'page'                 => 0,
            'itemsPerPage'         => 10,
            'entryType'            => 'all',
            'timeRange'            => 'all',
            'category'             => NULL,
            'includeSubCategories' => FALSE,
            'where'                => [],
            'request'              => []

        ], $params);

        $newsListing = Object\NewsEntry::getList();
        $newsListing->setOrderKey($settings['sort']['field']);
        $newsListing->setOrder($settings['sort']['dir']);
        $newsListing->setGroupBy('o_id');

        $categories = NULL;
        if ($settings['category'] && $settings['category'] instanceof Category) {
            $categories = static::getCategoriesRecursive($settings['category'],
                $settings['includeSubCategories']);
        }

        //add optional category selector
        static::addCategorySelectorToQuery($newsListing, $categories,
            $settings);

        //add timeRange
        static::addTimeRange($newsListing, $settings);

        //add entry type selector
        if ($settings['entryType'] !== 'all') {
            $newsListing->addConditionParam('entryType = ?',
                $settings['entryType']);
        }

        //add additional where clauses.
        if (count($settings['where'])) {
            foreach ($settings['where'] as $condition => $val) {
                $newsListing->addConditionParam($condition, $val);
            }
        }

        //do not allow empty names
        $newsListing->addConditionParam('name <> ?', '');

        //allow listing modification.
        static::modifyListing($newsListing, $settings);

        $paginator = \Zend_Paginator::factory($newsListing);
        $paginator->setCurrentPageNumber($settings['page']);
        $paginator->setItemCountPerPage($settings['itemsPerPage']);

        return $paginator;
    }

    /**
     * add time range restriction
     *
     * @param Object\NewsEntry\Listing $newsListing
     * @param array                    $settings
     */
    public static function addTimeRange($newsListing, $settings = [])
    {
        if (empty($settings['timeRange']) || $settings['timeRange'] === 'all') {
            return;
        }

        $identifier = '>=';
        if ($settings['timeRange'] === 'past') {
            $identifier = '<';
        }

        $newsListing->addConditionParam(sprintf('(
            CASE WHEN showEntryUntil IS NOT NULL
                THEN 
                    showEntryUntil %1$s UNIX_TIMESTAMP(NOW())
                ELSE
                    (CASE WHEN dateTo IS NOT NULL
                        THEN 
                            dateTo %1$s UNIX_TIMESTAMP(NOW()) 
                        ELSE
                            (CASE WHEN date IS NOT NULL
                                THEN 
                                    date %1$s UNIX_TIMESTAMP(NOW())  
                                ELSE
                                    FALSE
                                END
                            )
                        END
                    )
                END
            )
        ', $identifier)
        );
    }

    /**
     * add query join if categories available.
     *
     * @param Object\NewsEntry\Listing $newsListing
     * @param null                     $categories
     * @param array                    $settings
     */
    public static function addCategorySelectorToQuery(
        $newsListing,
        $categories = NULL,
        $settings = []
    ) {
        $newsListing->onCreateQuery(function (\Zend_Db_Select $query) use (
            $newsListing,
            $categories,
            $settings
        ) {
            if (!empty($categories)) {
                $query->join(
                    ['relations' => 'object_relations_' . $newsListing->getClassId()],
                    "relations.src_id = oo_id",
                    ''
                );
            }

            //allow query modification.
            static::modifyQuery($query, $newsListing, $settings);
        });

        if (!empty($categories)) {
            $newsListing->addConditionParam('relations.fieldname = "categories" AND relations.dest_id IN (' . rtrim(str_repeat('?,',
                    count($categories)), ',') . ')', $categories);
        }
    }

    /**
     * @param \Zend_Db_Select                         $query
     * @param \Pimcore\Model\Object\NewsEntry\Listing $listing
     * @param array                                   $settings
     */
    protected static function modifyQuery($query, $listing, $settings = [])
    {
    }

    /**
     * @param \Pimcore\Model\Object\NewsEntry\Listing $listing
     * @param array                                   $settings
     */
    protected static function modifyListing($listing, $settings = [])
    {
    }

    /**
     * @param \Pimcore\Model\Object\NewsCategory $category
     * @param bool                               $includeSubCategories
     *
     * @return array|null
     */
    public static function getCategoriesRecursive(
        $category,
        $includeSubCategories = FALSE
    ) {
        if (!$category) {
            return NULL;
        }

        $categories = [$category->getId()];

        if ($includeSubCategories === TRUE) {

            $entries = NewsCategory::getList();
            $entries->setCondition('o_path LIKE "' . $category->getFullPath() . '%"');

            foreach ($entries->load() as $entry) {
                $categories[] = $entry->getId();
            }
        }

        return array_values($categories);
    }

    /**
     * Get Image for Product
     *
     * @return bool|\Pimcore\Model\Asset
     */
    public function getImage()
    {
        if (count($this->getImages()) > 0) {
            return $this->getImages()[0];
        }

        return NULL;
    }

    public function getJsonLDData()
    {
        $data = [
            '@context'      => 'http://schema.org/',
            '@type'         => 'NewsArticle',
            'datePublished' => $this->getDate()->format('Y-m-d'),
            'headline'      => $this->getName(),
            'description'   => $this->getLead(),
            'articleBody'   => $this->getDescription()
        ];

        if ($this->getAuthor()) {
            $data['author'] = $this->getAuthor();
        }

        if (count($this->getImages()) > 0) {

            $image = $this->getImages()[0];

            if ($image instanceof Image) {
                $data['image'] = [
                    '@type'  => 'ImageObject',
                    'url'    => \Pimcore\Tool::getHostUrl() . $image->getThumbnail('galleryImage')
                            ->getPath(),
                    'width'  => $image->getThumbnail('galleryImage')
                        ->getWidth(),
                    'height' => $image->getThumbnail('galleryImage')
                        ->getHeight(),
                ];
            }
        }

        return $data;
    }

}
