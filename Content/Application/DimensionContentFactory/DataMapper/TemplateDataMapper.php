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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class TemplateDataMapper implements DataMapperInterface
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
        object $unlocalizedObject,
        ?object $localizedObject = null
    ): void {
        if (!$unlocalizedObject instanceof TemplateInterface) {
            return;
        }

        if (!isset($data['template'])) {
            throw new \RuntimeException('Expected "template" to be set in the data array.');
        }

        $template = $data['template'];

        list($unlocalizedData, $localizedData) = $this->getTemplateData(
            $data,
            $unlocalizedObject->getTemplateType(),
            $template
        );

        if ($localizedObject) {
            if (!$localizedObject instanceof TemplateInterface) {
                throw new \RuntimeException(sprintf('Expected "$localizedDimensionContent" from type "%s" but "%s" given.', TemplateInterface::class, \get_class($localizedObject)));
            }

            $localizedObject->setTemplateKey($template);
            $localizedObject->setTemplateData($localizedData);
        }

        if (!$localizedObject) {
            // Only set templateKey to unlocalizedDimension when no localizedDimension exist
            $unlocalizedObject->setTemplateKey($template);
        }

        // Unlocalized dimensions can contain data of different templates so we need to merge them together
        $unlocalizedObject->setTemplateData(array_merge(
            $unlocalizedObject->getTemplateData(),
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
            throw new \RuntimeException(sprintf('Could not find structure "%s" of type "%s".', $template, $type));
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

            if (\array_key_exists($name, $data)) {
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
