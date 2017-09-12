<?php

namespace NewsBundle\Model;

use CoreShop\Component\Resource\ImplementedByPimcoreException;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset\Image;
use Zend\Paginator\Paginator;

class Entry extends DataObject\Concrete implements EntryInterface
{
    /**
     * Get all News
     * @return array
     */
    public static function getAll()
    {
        $newsListing = DataObject\NewsEntry::getList();
        static::modifyListing($newsListing);

        return $newsListing->getObjects();
    }

    /**
     * Get News from the Category with Paging
     *
     * @param array $params
     *
     * @return Paginator
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

        $newsListing = DataObject\NewsEntry::getList();
        $newsListing->setOrderKey($settings['sort']['field']);
        $newsListing->setOrder($settings['sort']['dir']);
        $newsListing->setGroupBy('o_id');

        $categories = NULL;
        if ($settings['category'] && $settings['category'] instanceof Category) {
            $categories = static::getCategoriesRecursive($settings['category'], $settings['includeSubCategories']);
        }

        //add optional category selector
        static::addCategorySelectorToQuery($newsListing, $categories, $settings);

        //add timeRange
        static::addTimeRange($newsListing, $settings);

        //add entry type selector
        if ($settings['entryType'] !== 'all') {
            $newsListing->addConditionParam('entryType = ?', $settings['entryType']);
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

        $paginator = new Paginator($newsListing);
        $paginator->setCurrentPageNumber($settings['page']);
        $paginator->setItemCountPerPage($settings['itemsPerPage']);

        return $paginator;
    }

    /**
     * add time range restriction
     *
     * @param DataObject\NewsEntry\Listing $newsListing
     * @param array                        $settings
     */
    public static function addTimeRange($newsListing, $settings = [])
    {
        if (empty($settings['timeRange']) || $settings['timeRange'] === 'all') {
            return;
        }

        $pointer = '>=';
        if ($settings['timeRange'] === 'past') {
            $pointer = '<';
        }

        $newsListing->addConditionParam('(
            ( showEntryUntil IS NOT NULL AND showEntryUntil ' . $pointer . ' UNIX_TIMESTAMP(NOW()) ) OR
            ( dateTo IS NOT NULL AND dateTo ' . $pointer . ' UNIX_TIMESTAMP(NOW()) ) OR
            ( date IS NOT NULL AND date ' . $pointer . ' UNIX_TIMESTAMP(NOW()) )
        )');
    }

    /**
     * add query join if categories available.
     *
     * @param DataObject\NewsEntry\Listing $newsListing
     * @param null                         $categories
     * @param array                        $settings
     */
    public static function addCategorySelectorToQuery($newsListing, $categories = NULL, $settings = [])
    {
        $newsListing->onCreateQuery(function (QueryBuilder $query) use ($newsListing, $categories, $settings) {
            if (!empty($categories)) {
                $query->join(
                    ['relations' => 'object_relations_' . $newsListing->getClassId()],
                    "relations.src_id = o_id",
                    ''
                );
            }

            //allow query modification.
            static::modifyQuery($query, $newsListing, $settings);
        });

        if (!empty($categories)) {
            $newsListing->addConditionParam('relations.fieldname = "categories" AND relations.dest_id IN (' . rtrim(str_repeat('?,', count($categories)), ',') . ')', $categories);
        }
    }

    /**
     * @param QueryBuilder                                $query
     * @param \Pimcore\Model\DataObject\NewsEntry\Listing $listing
     * @param array                                       $settings
     */
    protected static function modifyQuery($query, $listing, $settings = [])
    {
    }

    /**
     * @param \Pimcore\Model\DataObject\NewsEntry\Listing $listing
     * @param array                                       $settings
     */
    protected static function modifyListing($listing, $settings = [])
    {
    }

    /**
     * @param \Pimcore\Model\DataObject\NewsCategory $category
     * @param bool                                   $includeSubCategories
     *
     * @return array|null
     */
    public static function getCategoriesRecursive($category, $includeSubCategories = FALSE)
    {
        if (!$category) {
            return NULL;
        }

        $categories = [$category->getId()];

        if ($includeSubCategories === TRUE) {

            $entries = DataObject\NewsCategory::getList();
            $entries->setCondition('o_path LIKE "' . $category->getFullPath() . '%"');

            foreach ($entries->load() as $entry) {
                $categories[] = $entry->getId();
            }
        }

        return array_values($categories);
    }

    /**
     * Get single image for entry
     * @return bool|\Pimcore\Model\Asset
     */
    public function getImage()
    {
        if (count($this->getImages()) > 0) {
            return $this->getImages()[0];
        }

        return NULL;
    }

    /**
     * @return array
     */
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
                    'url'    => \Pimcore\Tool::getHostUrl() . $image->getThumbnail('galleryImage')->getPath(),
                    'width'  => $image->getThumbnail('galleryImage')->getWidth(),
                    'height' => $image->getThumbnail('galleryImage')->getHeight(),
                ];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName($language = NULL)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getLead($language = NULL)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($language = NULL)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectLink($language = NULL)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailUrl($language = NULL)
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntryType()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaTitle()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        throw new ImplementedByPimcoreException(__CLASS__, __METHOD__);
    }
}
