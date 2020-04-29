# Upgrade

## master

### Refactored the ContentViewBuilder

The class and its interface was renamed from `ContentViewBuilder` & `ContentViewBuilderInterface` 
to `ContentViewBuilderFactory` & `ContentViewBuilderFactoryInterface`.

The service has been renamed from `sulu_content.content_view_builder` to `sulu_content.content_view_builder_factory`.

The function `build` was replaced by `createContentRichViews` and additional functions has been introduced.

__BEFORE:__
```php
$this->contentViewBuilder->build(
    $viewCollection,
    $resourceKey,
    Example::TEMPLATE_TYPE,
    static::EDIT_TABS_VIEW,
    static::ADD_TABS_VIEW
);
```

__AFTER:__
```php
$viewBuilders = $this->contentViewBuilderFactory->createContentRichViews(
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
