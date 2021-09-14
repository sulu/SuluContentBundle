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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait CreateExampleTrait
{
    public function createExample(): Example
    {
        $example = new Example();

        static::getEntityManager()->persist($example);

        return $example;
    }

    /**
     * @param array{
     *     locale?: ?string,
     *     stage?: ?string,
     *     templateKey?: ?string,
     *     templateData?: mixed[],
     *     excerptCategories?: CategoryInterface[],
     *     excerptTags?: TagInterface[],
     * } $data
     */
    public function createExampleContent(Example $example, array $data = []): void
    {
        $locale = $data['locale'] ?? 'en';
        $stage = $data['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        /** @var ExampleDimensionContent $unlocalizedDimensionContent */
        $unlocalizedDimensionContent = $example->createDimensionContent();
        $unlocalizedDimensionContent->setStage($stage);
        $example->addDimensionContent($unlocalizedDimensionContent);

        /** @var ExampleDimensionContent $localizedDimensionContent */
        $localizedDimensionContent = $example->createDimensionContent();
        $localizedDimensionContent->setLocale($locale);
        $localizedDimensionContent->setStage($stage);
        $localizedDimensionContent->setTitle($data['title'] ?? null);

        $templateKey = $data['templateKey'] ?? null;
        if ($templateKey) {
            $localizedDimensionContent->setTemplateKey($templateKey);
        }
        $localizedDimensionContent->setTemplateData($data['templateData'] ?? ['title' => '']);
        $localizedDimensionContent->setExcerptCategories($data['excerptCategories'] ?? []);
        $localizedDimensionContent->setExcerptTags($data['excerptTags'] ?? []);

        $example->addDimensionContent($localizedDimensionContent);
    }

    abstract protected static function getEntityManager(): EntityManagerInterface;

    abstract protected static function getContainer(): ContainerInterface;
}
