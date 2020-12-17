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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableTrait;

class RoutableTraitTest extends TestCase
{
    protected function getRoutableInstance(): RoutableInterface
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('content-id-123');

        return new class($contentRichEntity->reveal()) implements RoutableInterface {
            use RoutableTrait;

            /**
             * @var ContentRichEntityInterface
             */
            private $resource;

            public function __construct(ContentRichEntityInterface $resource)
            {
                $this->resource = $resource;
            }

            public static function getResourceKey(): string
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }

            public function getLocale(): string
            {
                return 'en';
            }

            public function getResource(): ContentRichEntityInterface
            {
                return $this->resource;
            }
        };
    }

    public function testGetLocale(): void
    {
        $model = $this->getRoutableInstance();
        $this->assertSame('en', $model->getLocale());
    }

    public function testGetResourceId(): void
    {
        $model = $this->getRoutableInstance();
        $this->assertSame('content-id-123', $model->getResourceId());
    }
}
