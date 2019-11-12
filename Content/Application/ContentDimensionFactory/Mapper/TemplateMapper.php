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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class TemplateMapper implements MapperInterface
{
    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    public function __construct(StructureMetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function map(
        array $data,
        object $contentDimension,
        ?object $localizedContentDimension = null
    ): void {
        if (!$contentDimension instanceof TemplateInterface) {
            return;
        }

        if (!isset($data['template'])) {
            throw new \RuntimeException('Expected "template" to be set in the data array.');
        }

        $template = $data['template'];

        list($unlocalizedData, $localizedData) = $this->getTemplateData(
            $data,
            $contentDimension->getTemplateType(),
            $template
        );

        if ($localizedContentDimension) {
            if (!$localizedContentDimension instanceof TemplateInterface) {
                throw new \RuntimeException(sprintf(
                    'Expected "$localizedContentDimension" from type "%s" but "%s" given.',
                    TemplateInterface::class,
                    \get_class($localizedContentDimension)
                ));
            }

            $localizedContentDimension->setTemplateKey($template);
            $localizedContentDimension->setTemplateData($localizedData);
        }

        if (!$localizedContentDimension) {
            // Only set templateKey to unlocalizedDimension when no localizedDimension exist
            $contentDimension->setTemplateKey($template);
        }

        // Unlocalized dimensions can contain data of different templates so we need to merge them together
        $contentDimension->setTemplateData(array_merge(
            $contentDimension->getTemplateData(),
            $unlocalizedData
        ));
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function getTemplateData(array $data, string $type, string $template): array
    {
        $metadata = $this->factory->getStructureMetadata($type, $template);

        if (!$metadata) {
            throw new \RuntimeException(sprintf(
                'Could not find structure "%s" of type "%s".',
                $template,
                $type
            ));
        }

        $unlocalizedData = [];
        $localizedData = [];

        foreach ($metadata->getProperties() as $property) {
            $value = null;
            $name = $property->getName();

            // Float are converted to ints in php array as key so we need convert it to string
            if (\is_float($name)) {
                $name = (string) $name;
            }

            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            }

            if ($property->isLocalized()) {
                $localizedData[$name] = $value;
                continue;
            }

            $unlocalizedData[$name] = $value;
        }

        return [$unlocalizedData, $localizedData];
    }
}
