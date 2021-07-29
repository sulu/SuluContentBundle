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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDataMapper\DataMapper;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\AuthorDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContactFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AuthorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class AuthorDataMapperTest extends TestCase
{
    protected function createAuthorDataMapperInstance(ContactFactoryInterface $contactFactory): AuthorDataMapper
    {
        return new AuthorDataMapper($contactFactory);
    }

    public function testMapNoAuthor(): void
    {
        $data = [
            'author' => 1,
            'authored' => '2020-05-08T00:00:00+00:00',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()
            ->shouldBeCalled()
            ->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->shouldBeCalled()
            ->willReturn($unlocalizedDimensionContent);

        $contactFactory = $this->prophesize(ContactFactoryInterface::class);

        $authorMapper = $this->createAuthorDataMapperInstance($contactFactory->reveal());
        $authorMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedNoAuthor(): void
    {
        $data = [
            'author' => 1,
            'authored' => '2020-05-08T00:00:00+00:00',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(AuthorInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()
            ->shouldBeCalled()
            ->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent->reveal());

        $contactFactory = $this->prophesize(ContactFactoryInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected "$localizedObject" from type "Sulu\Bundle\ContentBundle\Content\Domain\Model\AuthorInterface" but "Double\DimensionContentInterface\P3" given.');

        $authorMapper = $this->createAuthorDataMapperInstance($contactFactory->reveal());
        $authorMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapUnlocalizedAuthor(): void
    {
        $data = [
            'author' => 1,
            'authored' => '2020-05-08T00:00:00+00:00',
        ];
        $contact = $this->prophesize(ContactInterface::class);
        $contactFactory = $this->prophesize(ContactFactoryInterface::class);
        $contactFactory->create(1)
            ->shouldBeCalled()
            ->willReturn($contact->reveal());

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(AuthorInterface::class);

        $unlocalizedDimensionContent->setAuthor($contact->reveal())
            ->shouldBeCalled();
        $unlocalizedDimensionContent->setAuthored(Argument::that(
            function (DateTimeImmutable $date) {
                return $date->getTimestamp() === (new \DateTimeImmutable('2020-05-08T00:00:00+00:00'))->getTimestamp();
            })
        )->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()
            ->shouldBeCalled()
            ->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent->reveal());
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $authorMapper = $this->createAuthorDataMapperInstance($contactFactory->reveal());
        $authorMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedAuthor(): void
    {
        $data = [
            'author' => 1,
            'authored' => '2020-05-08T00:00:00+00:00',
        ];
        $contact = $this->prophesize(ContactInterface::class);
        $contactFactory = $this->prophesize(ContactFactoryInterface::class);
        $contactFactory->create(1)
            ->shouldBeCalled()
            ->willReturn($contact->reveal());

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(AuthorInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(AuthorInterface::class);

        $localizedDimensionContent->setAuthor($contact->reveal())
            ->shouldBeCalled();
        $localizedDimensionContent->setAuthored(Argument::that(
            function (DateTimeImmutable $date) {
                return $date->getTimestamp() === (new \DateTimeImmutable('2020-05-08T00:00:00+00:00'))->getTimestamp();
            })
        )->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()
            ->shouldBeCalled()
            ->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent->reveal());
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent->reveal());

        $authorMapper = $this->createAuthorDataMapperInstance($contactFactory->reveal());
        $authorMapper->map($data, $dimensionContentCollection->reveal());
    }
}
