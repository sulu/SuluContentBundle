# Upgrade

## 0.5.0

<<<<<<< HEAD
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
=======
### Dimension Entity was removed

The `Dimension` entity was removed because it had no additional value and did make things
unnecessary complex.

#### Migrate data into your entity

As the Dimension Entity did contain locale and stage in which your DimensionContent is saved
this data need to be migrated into your own entity.

```sql
# Create stage and locale fields
ALTER TABLE test_example_dimension_contents ADD stage VARCHAR(16) DEFAULT NULL, ADD locale VARCHAR(5) DEFAULT NULL;

# Migrate data to new fields
UPDATE test_example_dimension_contents myContentDimension
INNER JOIN cn_dimensions dimension ON dimension.no = myContentDimension.dimension_id
SET myContentDimension.stage = dimension.stage, myContentDimension.locale = dimension.locale;

# Remove nullable from stage field
ALTER TABLE test_example_dimension_contents CHANGE stage stage VARCHAR(16) NOT NULL;

# Remove dimension relation
ALTER TABLE test_example_dimension_contents DROP FOREIGN KEY FK_9BFA55B277428AD;
DROP INDEX IDX_9BFA55B277428AD ON test_example_dimension_contents;
ALTER TABLE test_example_dimension_contents DROP dimension_id;

# Drop Dimension Table
DROP TABLE cn_dimensions;
```

TODO provide here a general doctrine migration which support up/down.

#### Update your Content Entities

In your entity you need to change the createDimensionContent method:

```diff
-    public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
+    public function createDimensionContent(): DimensionContentInterface
     {
-        return new ExampleDimensionContent($this, $dimension);
+        $exampleDimensionContent = new ExampleDimensionContent($this);
+
+        return $exampleDimensionContent;
     }
```

Also the constructor of your "DimensionContent" entity need to be changed:

```diff
-    public function __construct(Example $example, DimensionInterface $dimension)
+    public function __construct(Example $example)
     {
         $this->example = $example;
-        $this->dimension = $dimension;
     }
```

The `DimensionContentInterface` has the `getDimension` removed and will now directly
need to provide the `getStage`, `setStage`, `getLocale` and `setLocale` methods.
This is normally done by the traits provided by the ContentBundle.

#### Update your list configuration

If you use the dimension data to be listed in your list you need to change it the following way:

```diff
<?xml version="1.0" ?>
<list xmlns="http://schemas.sulu.io/list-builder/list">
    <key>examples</key>

-    <joins name="dimensionContent" ref="dimension">
+    <joins name="dimensionContent">
        <join>
            <entity-name>dimensionContent</entity-name>
            <field-name>Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example.dimensionContents</field-name>
            <method>LEFT</method>
-            <condition>dimensionContent.dimension = %sulu.model.dimension.class%.no</condition>
+            <condition>dimensionContent.locale = :locale AND dimensionContent.stage = 'draft'</condition>
        </join>
    </joins>
    
-    <joins name="dimension">
-        <join>
-            <entity-name>%sulu.model.dimension.class%</entity-name>
-            <condition>%sulu.model.dimension.class%.locale = :locale AND %sulu.model.dimension.class%.stage = 'draft'</condition>
+            <condition>dimensionContent.locale = :locale AND dimensionContent.stage = 'draft'</condition>
-         </join>
-     </joins>

    <properties>
        <property name="id" translation="sulu_admin.id">
            <field-name>id</field-name>
            <entity-name>Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example</entity-name>
        </property>

-        <property name="dimensionId" visibility="never">
-            <field-name>id</field-name>
-            <entity-name>%sulu.model.dimension.class%</entity-name>
-
-            <joins ref="dimension"/>
-        </property>
-
        <property name="title" visibility="yes" translation="sulu_admin.title">
            <field-name>title</field-name>
            <entity-name>dimensionContent</entity-name>

            <joins ref="dimensionContent"/>
        </property>
    </properties>
</list>
>>>>>>> 1ac7236... Remove dimension entity
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
