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

namespace Sulu\Bundle\ContentBundle\Tests\Traits;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapper;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Webmozart\Assert\Assert;

trait CreateExampleTrait
{
    /**
     * @param array{de?: mixed, en?: mixed} $dataSet
     * @param array{create_route?: bool} $options
     */
    protected static function createExample(array $dataSet = [], array $options = []): Example
    {
        $entityManager = static::getEntityManager();
        /** @var ContentDataMapperInterface $contentDataMapper */
        $contentDataMapper = new ContentDataMapper([
            static::getContainer()->get('sulu_content.template_data_mapper'),
            static::getContainer()->get('sulu_content.excerpt_data_mapper'),
            static::getContainer()->get('sulu_content.seo_data_mapper'),
            static::getContainer()->get('sulu_content.workflow_data_mapper'),
            // for performance reasons we avoid here route mapper and create the route manually when needed
        ]);

        $example = new Example();

        if ($options['create_route'] ?? false) {
            Assert::isInstanceOf($entityManager, EntityManager::class);
            $entityManager->persist($example);
            $entityManager->flush($example); // we need an id for creating the route
        }

        $slugger = new AsciiSlugger();

        $fillWithdefaultData = function (array $data) use ($slugger): array {
            // the example default template has the following required fields
            $data['title'] = $data['title'] ?? 'Test Example';
            $data['url'] = $data['url'] ?? '/' . $slugger->slug($data['title'])->toString();
            $data['description'] = $data['description'] ?? null;
            $data['image'] = $data['image'] ?? null;
            $data['article'] = $data['article'] ?? null;
            $data['blocks'] = $data['blocks'] ?? [];

            return $data;
        };

        if (\count($dataSet)) {
            $draftUnlocalizedDimension = new ExampleDimensionContent($example);
            $example->addDimensionContent($draftUnlocalizedDimension);
            $entityManager->persist($draftUnlocalizedDimension);
        }

        $createdPublishedUnlocalizedDimension = false;
        foreach ($dataSet as $locale => $data) {
            $published = $data['published'] ?? false;
            unset($data['published']);

            // draft data
            $draft = $data['draft'] ?? null;
            unset($data['draft']);

            // create localized live dimension
            $draftLocalizedDimension = new ExampleDimensionContent($example);
            $draftLocalizedDimension->setLocale($locale);
            $example->addDimensionContent($draftLocalizedDimension);
            $entityManager->persist($draftLocalizedDimension);

            // Map Draft Data
            $contentDataMapper->map($fillWithdefaultData($draft ?: $data), $draftUnlocalizedDimension, $draftLocalizedDimension);

            if ($draft) {
                $draftLocalizedDimension->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_DRAFT);
            }

            if ($published) {
                // create unlocalized live dimension
                if (!$createdPublishedUnlocalizedDimension) {
                    // create localized live dimension
                    $liveUnlocalizedDimension = new ExampleDimensionContent($example);
                    $liveUnlocalizedDimension->setStage(DimensionContentInterface::STAGE_LIVE);
                    $example->addDimensionContent($liveUnlocalizedDimension);
                    $entityManager->persist($liveUnlocalizedDimension);
                    $createdPublishedUnlocalizedDimension = true;
                }

                // create localized live dimension
                $liveLocalizedDimension = new ExampleDimensionContent($example);
                $liveLocalizedDimension->setStage(DimensionContentInterface::STAGE_LIVE);
                $liveLocalizedDimension->setLocale($locale);
                $example->addDimensionContent($liveLocalizedDimension);
                $entityManager->persist($liveLocalizedDimension);

                // set published state
                if (!$draft) {
                    $draftLocalizedDimension->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
                }

                $draftLocalizedDimension->setWorkflowPublished(new \DateTimeImmutable());
                $liveLocalizedDimension->setWorkflowPublished(new \DateTimeImmutable());

                // map data
                $data['published'] = date('Y-m-d H:i:s');
                $contentDataMapper->map($fillWithdefaultData($data), $liveUnlocalizedDimension, $liveLocalizedDimension);

                if ($options['create_route'] ?? false) {
                    $route = new Route();
                    $route->setLocale($locale);
                    $route->setPath($draftLocalizedDimension->getTemplateData()['url']);
                    $route->setEntityId($example->getId());
                    $route->setEntityClass(\get_class($example));

                    $entityManager->persist($route);
                }
            }
        }

        $entityManager->persist($example);

        return $example;
    }

    abstract protected static function getEntityManager(): EntityManagerInterface;
}
