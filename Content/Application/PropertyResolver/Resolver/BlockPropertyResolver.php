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

namespace Sulu\Bundle\ContentBundle\Content\Application\PropertyResolver\Resolver;

use Psr\Log\LoggerInterface;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Bundle\ContentBundle\Content\Application\MetadataResolver\MetadataResolver;
use Sulu\Bundle\ContentBundle\Content\Application\PropertyResolver\PropertyResolverInterface;

class BlockPropertyResolver implements PropertyResolverInterface
{
    private MetadataResolver $metadataResolver;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $debug = false,
    ) {
    }

    /**
     * Prevent circular dependency by injecting the MetadataResolver after instantiation.
     */
    public function setMetadataResolver(MetadataResolver $metadataResolver): void
    {
        $this->metadataResolver = $metadataResolver;
    }

    /**
     * @param non-empty-array<array<mixed>> $data
     */
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        $metadata = $params['metadata'] ?? null;
        \assert($metadata instanceof FieldMetadata, 'Metadata must be set to resolve hotspots.');
        $metadataTypes = $metadata->getTypes();
        $contentViews = [];
        foreach ($data as $key => $block) {
            if (!\is_array($block) || !isset($block['type']) || !\is_string($block['type'])) {
                continue;
            }

            $type = $block['type'];
            $formMetadata = $metadataTypes[$type] ?? null;

            if (!$formMetadata instanceof FormMetadata) {
                $errorMessage = \sprintf(
                    'Metadata type "%s" in "%s" not found, founded types are: "%s"',
                    $type,
                    $metadata->getName(),
                    \implode('", "', \array_keys($metadataTypes)),
                );

                $this->logger->error($errorMessage);

                if ($this->debug) {
                    throw new \UnexpectedValueException($errorMessage);
                }

                $type = $metadata->getDefaultType();
                $formMetadata = $metadataTypes[$type] ?? null;
                if (!$formMetadata instanceof FormMetadata) {
                    continue;
                }
            }

            $contentViews[$key] = ContentView::create(
                \array_merge(
                    ['type' => $type],
                    $this->metadataResolver->resolveItems($formMetadata->getItems(), $block, $locale)
                ),
                []
            );
        }

        return ContentView::create($contentViews, []);
    }

    public static function getType(): string
    {
        return 'block';
    }
}
