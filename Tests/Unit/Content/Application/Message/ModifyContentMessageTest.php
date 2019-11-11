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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\Message;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\Message\ModifyContentMessage;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

class ModifyContentMessageTest extends TestCase
{
    /**
     * @param mixed[] $data
     * @param array<string, string|int|float|bool|null> $dimensionAttributes
     */
    protected function createModifyContentMessageInstance(
        ContentInterface $content,
        array $data,
        array $dimensionAttributes
    ): ModifyContentMessage {
        return new ModifyContentMessage($content, $data, $dimensionAttributes);
    }

    protected function createContentInstance(): ContentInterface
    {
        return new class() extends AbstractContent {
            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimension(string $dimensionId): ContentDimensionInterface
            {
                throw new \RuntimeException('Should not be called');
            }
        };
    }

    public function testGetContent(): void
    {
        $content = $this->createContentInstance();
        $createContentMessage = $this->createModifyContentMessageInstance($content, [], []);

        $this->assertSame($content, $createContentMessage->getContent());
    }

    public function testGetData(): void
    {
        $content = $this->createContentInstance();
        $createContentMessage = $this->createModifyContentMessageInstance($content, [
            'data' => 'value',
        ], []);

        $this->assertSame([
            'data' => 'value',
        ], $createContentMessage->getData());
    }

    public function testGetDimensionAttributes(): void
    {
        $content = $this->createContentInstance();
        $createContentMessage = $this->createModifyContentMessageInstance($content, [], [
            'locale' => 'de',
        ]);

        $this->assertSame([
            'locale' => 'de',
        ], $createContentMessage->getDimensionAttributes());
    }
}
