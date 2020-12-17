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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
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

        $example = new Example();

        if ($options['create_route'] ?? false) {
            Assert::isInstanceOf($entityManager, EntityManager::class);
            $entityManager->persist($example);
            $entityManager->flush($example); // we need an id for creating the route
        }

        $slugger = new AsciiSlugger();

        if (\count($dataSet)) {
            $draftUnlocalizedDimension = new ExampleDimensionContent($example);
            $example->addDimensionContent($draftUnlocalizedDimension);
            $entityManager->persist($draftUnlocalizedDimension);
        }

        $createdPublishedUnlocalizedDimension = false;
        foreach ($dataSet as $locale => $data) {
            $published = $data['published'] ?? false;
            unset($data['published']);

            if ($published && !$createdPublishedUnlocalizedDimension) {
                $liveUnlocalizedDimension = new ExampleDimensionContent($example);
                $liveUnlocalizedDimension->setStage(DimensionContentInterface::STAGE_LIVE);
                $example->addDimensionContent($liveUnlocalizedDimension);
                $entityManager->persist($liveUnlocalizedDimension);
                $createdPublishedUnlocalizedDimension = true;
            }

            $draft = $data['draft'] ?? null;
            unset($data['draft']);

            $draftLocalizedDimension = new ExampleDimensionContent($example);

            $setDraftData = function (ExampleDimensionContent $draftLocalizedDimension, array $data) use ($entityManager, $slugger, $locale) {
                $templateKey = $data['template'] ?? 'default';
                unset($data['template']);
                /** @var CategoryInterface[] $excerptCategories */
                $excerptCategories = [];
                foreach ($data['excerptCategories'] ?? [] as $categoryId) {
                    /** @var CategoryInterface $category */
                    $category = $entityManager->getReference(CategoryInterface::class, $categoryId);
                    $excerptCategories[] = $category;
                }
                unset($data['excerptCategories']);
                /** @var TagInterface[] $excerptTags */
                $excerptTags = [];
                foreach ($data['excerptTags'] ?? [] as $tagId) {
                    /** @var TagInterface $tag */
                    $tag = $entityManager->getReference(TagInterface::class, $tagId);
                    $excerptTags[] = $tag;
                }
                unset($data['excerptTags']);

                $excerptTitle = $data['excerptTitle'] ?? null;
                unset($data['excerptTitle']);
                $excerptDescription = $data['excerptDescription'] ?? null;
                unset($data['excerptDescription']);
                $excerptMore = $data['excerptMore'] ?? null;
                unset($data['excerptMore']);

                // set required default fields
                $data['title'] = $data['title'] ?? 'Test Example';
                $data['url'] = $data['url'] ?? '/' . $slugger->slug($data['title'])->toString();
                $data['description'] = $data['description'] ?? null;
                $data['image'] = $data['image'] ?? null;
                $data['article'] = $data['article'] ?? null;
                $data['blocks'] = $data['blocks'] ?? [];

                $draftLocalizedDimension->setStage(DimensionContentInterface::STAGE_DRAFT);
                $draftLocalizedDimension->setLocale($locale);
                $draftLocalizedDimension->setTitle($data['title']);
                $draftLocalizedDimension->setTemplateData($data);
                $draftLocalizedDimension->setTemplateKey($templateKey);
                $draftLocalizedDimension->setExcerptTitle($excerptTitle);
                $draftLocalizedDimension->setExcerptDescription($excerptDescription);
                $draftLocalizedDimension->setExcerptMore($excerptMore);
                $draftLocalizedDimension->setExcerptCategories($excerptCategories);
                $draftLocalizedDimension->setExcerptTags($excerptTags);
                $draftLocalizedDimension->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);
            };

            $setDraftData($draftLocalizedDimension, $data);

            $example->addDimensionContent($draftLocalizedDimension);
            $entityManager->persist($draftLocalizedDimension);

            if ($published) {
                $draftLocalizedDimension->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
                $draftLocalizedDimension->setWorkflowPublished(new \DateTimeImmutable());

                $liveLocalizedDimension = new ExampleDimensionContent($example);
                $liveLocalizedDimension->setStage(DimensionContentInterface::STAGE_LIVE);
                $liveLocalizedDimension->setLocale($draftLocalizedDimension->getLocale());
                $liveLocalizedDimension->setTitle($draftLocalizedDimension->getTitle());
                $liveLocalizedDimension->setTemplateData($draftLocalizedDimension->getTemplateData());
                $liveLocalizedDimension->setTemplateKey($draftLocalizedDimension->getTemplateKey());
                $liveLocalizedDimension->setExcerptCategories($draftLocalizedDimension->getExcerptCategories());
                $liveLocalizedDimension->setExcerptTags($draftLocalizedDimension->getExcerptTags());
                $liveLocalizedDimension->setExcerptTitle($draftLocalizedDimension->getExcerptTitle());
                $liveLocalizedDimension->setExcerptDescription($draftLocalizedDimension->getExcerptDescription());
                $liveLocalizedDimension->setExcerptMore($draftLocalizedDimension->getExcerptMore());
                $liveLocalizedDimension->setWorkflowPublished(new \DateTimeImmutable());
                $example->addDimensionContent($liveLocalizedDimension);
                $entityManager->persist($liveLocalizedDimension);

                if ($options['create_route'] ?? false) {
                    $route = new Route();
                    $route->setLocale($locale);
                    $route->setPath($draftLocalizedDimension->getTemplateData()['url']);
                    $route->setEntityId($example->getId());
                    $route->setEntityClass(\get_class($example));

                    $entityManager->persist($route);
                }

                if ($draft) {
                    $setDraftData($draftLocalizedDimension, array_merge($data, $draft));
                    $draftLocalizedDimension->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_DRAFT);
                }
            }
        }

        $entityManager->persist($example);

        return $example;
    }

    abstract protected static function getEntityManager(): EntityManagerInterface;
}
