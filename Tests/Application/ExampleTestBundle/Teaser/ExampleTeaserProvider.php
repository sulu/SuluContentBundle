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

namespace Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Teaser;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Teaser\ContentTeaserProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\PageBundle\Teaser\Configuration\TeaserConfiguration;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExampleTeaserProvider extends ContentTeaserProvider
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        ContentManagerInterface $contentManager,
        StructureMetadataFactoryInterface $metadataFactory,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        parent::__construct($contentManager, $metadataFactory);

        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @param mixed[] $ids
     *
     * @return Example[]
     */
    protected function findByIds(array $ids): array
    {
        $repository = $this->entityManager->getRepository(Example::class);

        $examples = $repository->findBy(['id' => $ids]);

        $idPositions = array_flip($ids);

        usort($examples, function (Example $a, Example $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $examples;
    }

    protected function getResourceKey(): string
    {
        return Example::RESOURCE_KEY;
    }

    public function getConfiguration(): TeaserConfiguration
    {
        return new TeaserConfiguration(
            $this->translator->trans('example_test.example', [], 'admin'),
            $this->getResourceKey(),
            'table',
            ['title'],
            $this->translator->trans('example_test.select_examples', [], 'admin')
        );
    }

    protected function getDescription(ContentProjectionInterface $contentProjection, array $data): ?string
    {
        $article = strip_tags($data['article'] ?? '');

        return $article ?: parent::getDescription($contentProjection, $data);
    }
}
