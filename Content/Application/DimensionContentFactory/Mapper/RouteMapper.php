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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\Mapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\RouteBundle\Generator\RouteGeneratorInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class RouteMapper implements MapperInterface
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

    public function __construct(
        StructureMetadataFactoryInterface $factory,
        RouteGeneratorInterface $routeGenerator,
        RouteManagerInterface $routeManager
    ) {
        $this->factory = $factory;
        $this->routeGenerator = $routeGenerator;
        $this->routeManager = $routeManager;
    }

    public function map(
        array $data,
        object $dimensionContent,
        ?object $localizedDimensionContent = null
    ): void {
        if (!$localizedDimensionContent || !$localizedDimensionContent instanceof RoutableInterface) {
            return;
        }

        if (!$localizedDimensionContent instanceof TemplateInterface) {
            throw new \RuntimeException('ContentDimension needs to extend the TemplateInterface');
        }

        if (!isset($data['template'])) {
            throw new \RuntimeException('Expected "template" to be set in the data array.');
        }

        $template = $data['template'];
        $type = $localizedDimensionContent->getTemplateType();

        $metadata = $this->factory->getStructureMetadata($type, $template);
        if (!$metadata) {
            return;
        }

        $property = $this->getRouteProperty($metadata);
        if (!$property) {
            return;
        }

        if (!$localizedDimensionContent->getContentId()) {
            // FIXME the code only works if the content-dimension is flushed once and has a valid id

            return;
        }

        $locale = $localizedDimensionContent->getLocale();
        if (!$locale) {
            return;
        }

        /** @var string $name */
        $name = $property->getName();

        $currentRoutePath = $localizedDimensionContent->getTemplateData()[$name] ?? null;
        if (!\array_key_exists($name, $data) && null !== $currentRoutePath) {
            return;
        }

        $routePath = $data[$name] ?? null;
        if (!$routePath) {
            // FIXME this should be handled directly in the form - see pages as an example
            $routePath = $this->routeGenerator->generate(
                $localizedDimensionContent,
                ['route_schema' => '/{object.getTitle()}']
            );

            if ('/' === $routePath) {
                return;
            }

            $localizedDimensionContent->setTemplateData(
                array_merge(
                    $localizedDimensionContent->getTemplateData(),
                    [$name => $routePath]
                )
            );
        }

        $this->routeManager->createOrUpdateByAttributes(
            $localizedDimensionContent->getContentClass(),
            (string) $localizedDimensionContent->getContentId(),
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
