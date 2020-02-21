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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\RouteBundle\Generator\RouteGeneratorInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class RouteDataMapper implements DataMapperInterface
{
    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    /**
     * @var RouteGeneratorInterface
     */
    private $routeGenerator;

    /**
     * @var RouteManagerInterface
     */
    private $routeManager;

    /**
     * @var array<string, string>
     */
    private $structureDefaultTypes;

    public function __construct(
        StructureMetadataFactoryInterface $factory,
        RouteGeneratorInterface $routeGenerator,
        RouteManagerInterface $routeManager,
        array $structureDefaultTypes
    ) {
        $this->factory = $factory;
        $this->routeGenerator = $routeGenerator;
        $this->routeManager = $routeManager;
        $this->structureDefaultTypes = $structureDefaultTypes;
    }

    public function map(
        array $data,
        object $unlocalizedObject,
        ?object $localizedObject = null
    ): void {
        if (!$localizedObject || !$localizedObject instanceof RoutableInterface) {
            return;
        }

        if (!$localizedObject instanceof TemplateInterface) {
            throw new \RuntimeException('LocalizedObject needs to extend the TemplateInterface');
        }

        $type = $localizedObject->getTemplateType();

        /** @var string|null $template */
        $template = $data['template'] ?? null;

        if (null === $template) {
            $template = $this->structureDefaultTypes[$type] ?? null;
        }

        if (null === $template) {
            throw new \RuntimeException('Expected "template" to be set in the data array.');
        }

        $metadata = $this->factory->getStructureMetadata($type, $template);
        if (!$metadata) {
            return;
        }

        $property = $this->getRouteProperty($metadata);
        if (!$property) {
            return;
        }

        if (!$localizedObject->getContentId()) {
            // FIXME the code only works if the content-dimension is flushed once and has a valid id

            return;
        }

        $locale = $localizedObject->getLocale();
        if (!$locale) {
            return;
        }

        /** @var string $name */
        $name = $property->getName();

        $currentRoutePath = $localizedObject->getTemplateData()[$name] ?? null;
        if (!\array_key_exists($name, $data) && null !== $currentRoutePath) {
            return;
        }

        $routePath = $data[$name] ?? null;
        if (!$routePath) {
            // FIXME this should be handled directly in the form - see pages as an example
            $routePath = $this->routeGenerator->generate(
                $localizedObject,
                ['route_schema' => '/{object.getTitle()}']
            );

            if ('/' === $routePath) {
                return;
            }

            $localizedObject->setTemplateData(
                array_merge(
                    $localizedObject->getTemplateData(),
                    [$name => $routePath]
                )
            );
        }

        $this->routeManager->createOrUpdateByAttributes(
            $localizedObject->getContentClass(),
            (string) $localizedObject->getContentId(),
            $locale,
            $routePath
        );
    }

    private function getRouteProperty(StructureMetadata $metadata): ?PropertyMetadata
    {
        foreach ($metadata->getProperties() as $property) {
            if ('route' === $property->getType()) {
                return $property;
            }
        }

        return null;
    }
}
