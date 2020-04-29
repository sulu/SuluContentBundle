# Upgrade

## master

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
