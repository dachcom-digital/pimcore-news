# Custom Configuration
If the available configurability is not sufficient, you can now extend the configuration with your own fields.

### Eventlistener
Create an eventlistener that listens to the ``NewsEvents::NEWS_BRICK_QUERY_BUILD`` event
````<?php
  
  namespace AppBundle\EventListener;
  
  use NewsBundle\Event\NewsBrickEvent;
  use NewsBundle\NewsEvents;
  use Pimcore\Model\Document\PageSnippet;
  use Pimcore\Model\Document\Tag\Relations;
  use Pimcore\Templating\Renderer\TagRenderer;
  use Symfony\Component\EventDispatcher\EventSubscriberInterface;
  
  class NewsBrickModifierListener implements EventSubscriberInterface
  {
      /**
       * @var TagRenderer
       */
      protected $tagRenderer;
  
      /**
       * @param TagRenderer $tagRenderer
       */
      public function __construct(TagRenderer $tagRenderer)
      {
          $this->tagRenderer = $tagRenderer;
      }
  
      /**
       * @return array
       */
      public static function getSubscribedEvents()
      {
          return [
              NewsEvents::NEWS_BRICK_QUERY_BUILD => 'modifyBrick'
          ];
      }
  
      /**
       * @param NewsBrickEvent $event
       */
      public function modifyBrick(NewsBrickEvent $event)
      {
          $querySettings = $event->getQuerySettings();
          $document = $event->getInfo()->getDocument();
  
          $configuration = $this->getDocumentTag($document, '<yourpropertytype>', '<yourproperty>');
  
          # crucial: check properly
          if (!$configuration instanceof Relations) {
              return;
          }
  
          $elements = $configuration->getElements();
  
          $querySettings['<yourproperty>'] = $elements;
  
          $event->setQuerySettings($querySettings);
      }
  
      /**
       * @param PageSnippet $document
       * @param string      $type
       * @param string      $inputName
       * @param array       $options
       *
       * @return \Pimcore\Model\Document\Tag|null
       */
      protected function getDocumentTag(PageSnippet $document, $type, $inputName, array $options = [])
      {
          return $this->tagRenderer->getTag($document, $type, $inputName, $options);
      }
  }
````

### Service registration
````yaml
services:
    AppBundle\EventListener\NewsBrickModifierListener:
        autowire: true
        autoconfigure: true
        public: false
        tags:
            - { name: kernel.event_subscriber }
````

### TWIG
Create a new File in your app-resource-directory: NewsBundle/view/Areas/news/edit_custom.html.twig and use common pimcore-methods to add your configuration:

````twig
<div class="t-row">

    <div class="t-col-full">
        <label>{{ 'news.<yourpropertyname>'|trans({}, 'admin') }}</label>
        {{ pimcore_multihref('<yourproperty>', {
            'types'    : ['object', 'folder'],
            'width'    : '510px',
            'height'   : '150px',
            ...
        }) }}
    </div>

</div>
````

### Modify Listing
Create a class that extends ``\NewsBundle\Model\Entry`` and add this method to use your new configuration-values

`````php
/**
     * @param DataObject\NewsEntry\Listing $listing
     * @param array                        $settings
     */
    protected static function modifyListing($listing, $settings = [])
    {
        # for example, add a condition
        $listing->addConditionParam('<define your condition>');
    }
`````
