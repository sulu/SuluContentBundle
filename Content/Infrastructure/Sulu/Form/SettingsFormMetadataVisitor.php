<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Form;

use Sulu\Bundle\AdminBundle\FormMetadata\FormXmlLoader;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataVisitorInterface;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\LocalizedFormMetadataCollection;

class SettingsFormMetadataVisitor implements FormMetadataVisitorInterface
{
    /**
     * @var FormXmlLoader
     */
    private $formXmlLoader;

    /**
     * @var array<string, mixed[]>
     */
    private $formMappingTags;

    /**
     * @param array<string, mixed[]> $formMappingTags
     */
    public function __construct(FormXmlLoader $formXmlLoader, array $formMappingTags)
    {
        $this->formXmlLoader = $formXmlLoader;
        $this->formMappingTags = $formMappingTags;
    }

    public function visitFormMetadata(FormMetadata $formMetadata, string $locale, array $metadataOptions = []): void
    {
        if ('content_settings' === $formMetadata->getKey()) {
            usort($this->formMappingTags, static function ($a, $b) {
                return $b['priority'] <=> $a['priority'];
            });

            foreach ($this->formMappingTags as $tag) {
                $class = $metadataOptions['class'];
                if (is_subclass_of($class, $tag['instanceOf'])) {
                    /** @var LocalizedFormMetadataCollection $formMetadataCollection */
                    $formMetadataCollection = $this->formXmlLoader->load($tag['path']);
                    $settingsMetadata = $formMetadataCollection->getItems()[$locale];

                    $formMetadata->setItems(array_merge($formMetadata->getItems(), $settingsMetadata->getItems()));
                    $formMetadata->setSchema($formMetadata->getSchema()->merge($settingsMetadata->getSchema()));
                }
            }
        }
    }
}
