# Upgrade

## 0.5.0

### ContentTeaserProvider constructor changed

The constructor of the `ContentTeaserProvider` requires like the `ContentDataProviderRepository` the `show_drafts` 
parameter. In this case also the `getShowDrafts` was removed from the `ContentTeaserProvider` class.

**before**:

```yaml
    example_test.example_teaser_provider:
        class: Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Teaser\ExampleTeaserProvider
        public: true
        arguments:
            - '@sulu_content.content_manager'
            - '@doctrine.orm.entity_manager'
            - '@sulu_content.content_metadata_inspector'
            - '@sulu_page.structure.factory'
            - '@translator'
            - '%sulu_document_manager.show_drafts%'
        tags:
            - { name: sulu.teaser.provider, alias: examples }
```

```php
    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        ContentMetadataInspectorInterface $contentMetadataInspector,
        StructureMetadataFactoryInterface $metadataFactory,
        TranslatorInterface $translator
    ) {
        parent::__construct($contentManager, $entityManager, $contentMetadataInspector, $metadataFactory, Example::class);

        $this->translator = $translator;
    }
```

**after**:

```yaml
    example_test.example_teaser_provider:
        class: Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Teaser\ExampleTeaserProvider
        public: true
        arguments:
            - '@sulu_content.content_manager'
            - '@doctrine.orm.entity_manager'
            - '@sulu_content.content_metadata_inspector'
            - '@sulu_page.structure.factory'
            - '@translator'
            - '%sulu_document_manager.show_drafts%' # this was added
        tags:
            - { name: sulu.teaser.provider, alias: examples }
```

```php
    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        ContentMetadataInspectorInterface $contentMetadataInspector,
        StructureMetadataFactoryInterface $metadataFactory,
        TranslatorInterface $translator,
        bool $showDrafts // this was added
    ) {
        parent::__construct($contentManager, $entityManager, $contentMetadataInspector, $metadataFactory, Example::class, $showDrafts); // this was added

        $this->translator = $translator;
    }
```

## 0.4.0

### Rename getContentRichEntity method of DimensionContentInterface to getResource

The `getContentRichEntity` method of the `DimensionContentInterface` was renamed to `getResource`. 
This makes the naming consistent with the `getResourceKey` method of the `DimensionContentInterface` and 
the `getResourceId` method of the `RoutableInterface`.

### Rename getRoutableId method of RoutableInterface to getResourceId

The `getRoutableId` method of the `RoutableInterface` was renamed to `getResourceId`. This makes the naming consistent 
with the `getResourceKey` method of the `RoutableInterface` and the `DimensionContentInterface`.

### Add contentRichEntityClass parameter to getDefaultToolbarActions method of ContentViewBuilderFactory

The `getDefaultToolbarActions` method of the `ContentViewBuilderFactory` has a required `contentRichEntityClass` parameter
now. The parameter is used for determining the correct toolbar actions based on the implemented interfaces.

### Move static getResourceKey method from ContentRichEntityInterface to DimensionContentInterface

The static `getResourceKey` method was moved from the `ContentRichEntityInterface` to the `DimensionContentInterface`.
This makes it consistent with the `getTemplateType` method and the `getWorkflowName` method .

### Refactor RoutableInterface to use configured schema for route generation

The bundle now uses the `route_schema` that is configured via `sulu_route.mappings` for generating the route for an
entity instead of a hardcoded value. If no `route_schema` is configured, no route will be generated.

Therefore, the `getContentId` method of the `RoutableInterface` was renamed to `getRoutableId` and the 
`getContentClass` method was replaced with a `getResourceKey` method.

### Moved automation bundle services

The services related to the `SuluAutomationBundle` were moved to the 
`Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Automation` namespace. 
Furthermore the `ContentEntityPublishHandler` was renamed to `ContentPublishTaskHandler` and 
the `ContentEntityUnpublishHandler` was renamed to `ContentUnpublishTaskHandler`.

### Removed ContentProjection concept

To simplify the usage of the bundle, the ContentProjection concept was removed from the source code.
Therefore, the `ContentProjectionInterface` and the `ContentProjectionFactoryInterface` were removed.

Services that returned a `ContentProjectionInterface` instance were adjusted to return a merged
`DimensionContentInterface` instance. Furthermore, the `ContentMergerInterface::merge` method 
was refactored to accept a `DimensionContentCollectionInterface` parameter.

### Renamed merger services

* The `sulu_content.template_content_projection_factory_merger` was renamed to `sulu_content.template_merger`.
* The `sulu_content.workflow_content_projection_factory_merger` was renamed to `sulu_content.workflow_merger`.
* The `sulu_content.excerpt_content_projection_factory_merger` was renamed to `sulu_content.excerpt_merger`.
* The `sulu_content.seo_content_projection_factory_merger` was renamed to `sulu_content.seo_merger`.

## 0.3.0

### Refactored the ContentViewBuilder

The class and its interface was renamed from `ContentViewBuilder` & `ContentViewBuilderInterface` 
to `ContentViewBuilderFactory` & `ContentViewBuilderFactoryInterface`.

The service has been renamed from `sulu_content.content_view_builder` to `sulu_content.content_view_builder_factory`.

The function `build` was replaced by `createViews` and additional functions has been introduced.

The behaviour of the `createViews` function detects now the needed views: the template-view if the `TemplateInterface`
is implemented, the seo-view if the `SeoInterface` is implemented, and the excerpt-view if the `ExcerptInterface` is 
implemented.

**before**:

```php
$this->contentViewBuilder->build(
    $viewCollection,
    $resourceKey,
    Example::TEMPLATE_TYPE,
    static::EDIT_TABS_VIEW,
    static::ADD_TABS_VIEW
);
```

**after**:

```php
$viewBuilders = $this->contentViewBuilderFactory->createViews(
    Example::class,
    Example::TEMPLATE_TYPE,
    static::EDIT_TABS_VIEW,
    static::ADD_TABS_VIEW
);

foreach ($viewBuilders as $viewBuilder) {
    $viewCollection->add($viewBuilder);
}
```

### Changed the constructor of ContentObjectProvider class

The arguments of the constructor of the `ContentObjectProvider` class were changed.

### Renamed the `sulu_content.content_projection_factory_merger` tag to `sulu_content.merger`

The `sulu_content.content_projection_factory_merger` tag was renamed to `sulu_content.merger`.

### Renamed the `sulu_content.normalize_enhancer` tag to `sulu_content.normalizer`

The `sulu_content.normalize_enhancer` tag was renamed to `sulu_content.normalizer`.
