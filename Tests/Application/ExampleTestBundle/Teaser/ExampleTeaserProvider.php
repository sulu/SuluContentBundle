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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Teaser\ContentTeaserProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\PageBundle\Teaser\Configuration\TeaserConfiguration;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExampleTeaserProvider extends ContentTeaserProvider
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        StructureMetadataFactoryInterface $metadataFactory,
        TranslatorInterface $translator
    ) {
        parent::__construct($contentManager, $entityManager, $metadataFactory, Example::class);

        $this->translator = $translator;
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

    protected function getDescription(DimensionContentInterface $resolvedContent, array $data): ?string
    {
        $article = strip_tags($data['article'] ?? '');

        return $article ?: parent::getDescription($resolvedContent, $data);
    }
}
