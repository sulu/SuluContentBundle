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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Automation;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Automation\ContentPublishTaskHandler;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentPublishTaskHandlerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    private $entityManager;

    /**
     * @var ObjectRepository<ContentRichEntityInterface>|ObjectProphecy
     */
    private $repository;

    /**
     * @var ContentManagerInterface|ObjectProphecy
     */
    private $contentManager;
    /**
     * @var TranslatorInterface|ObjectProphecy
     */
    private $translator;

    /**
     * @var ContentPublishTaskHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(ObjectRepository::class); // @phpstan-ignore-line
        $this->contentManager = $this->prophesize(ContentManagerInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);

        $this->handler = new ContentPublishTaskHandler($this->contentManager->reveal(), $this->entityManager->reveal(), $this->translator->reveal());
    }

    public function testPublish(): void
    {
        /** @var ContentRichEntityInterface|ObjectProphecy $entity */
        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $class = ContentRichEntityInterface::class;
        $id = 1;
        $locale = 'en';

        $this->entityManager->getRepository(Argument::is($class))
            ->willReturn($this->repository->reveal())
            ->shouldBeCalled();

        $this->repository->findOneBy(Argument::is(['id' => $id]))
            ->willReturn($entity->reveal())
            ->shouldBeCalled();

        $this->contentManager->applyTransition(
            Argument::is($entity->reveal()),
            Argument::is(['locale' => $locale]),
            Argument::is(WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH)
        )->shouldBeCalled();

        $this->entityManager->flush()->shouldBeCalled();

        $this->handler->handle([
            'class' => $class,
            'id' => $id,
            'locale' => $locale,
        ]);
    }

    public function testConfiguration(): void
    {
        $this->translator->trans(Argument::type('string'), Argument::type('array'), Argument::exact('admin'))->willReturn('Publish');

        $configuration = $this->handler->getConfiguration();

        $this->assertSame('Publish', $configuration->getTitle());
    }

    public function testSupports(): void
    {
        /** @var ContentRichEntityInterface|ObjectProphecy $entity */
        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $this->assertTrue($this->handler->supports(\get_class($entity->reveal())));
        $this->assertFalse($this->handler->supports(PageDocument::class));
    }

    public function testConfigureOptionsResolver(): void
    {
        $optionsResolver = new OptionsResolver();

        $result = $this->handler->configureOptionsResolver($optionsResolver);
        $this->assertSame($optionsResolver, $result);
        $this->assertSame(['id', 'locale'], $optionsResolver->getRequiredOptions());
    }
}
