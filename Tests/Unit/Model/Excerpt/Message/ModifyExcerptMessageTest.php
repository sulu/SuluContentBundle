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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\Message;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\ModifyExcerptMessage;

class ModifyExcerptMessageTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testGetResourceKey(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame('resource-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame('en', $message->getLocale());
    }

    public function testGetTitle(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame('title-1', $message->getTitle());
    }

    public function testGetMore(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame('more-1', $message->getMore());
    }

    public function testGetDescription(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertNull($message->getDescription());
    }

    public function testGetCategoryIds(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame([1, 2, 3], $message->getCategoryIds());
    }

    public function testGetTagNames(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame(['tag-1', 'tag-2'], $message->getTagNames());
    }

    public function testGetIconMediaIds(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame([], $message->getIconMediaIds());
    }

    public function testGetImageMediaIds(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $this->assertSame([5, 6], $message->getImageMediaIds());
    }

    public function testGetExcerpt(): void
    {
        $this->expectException(MissingResultException::class);

        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $message->getExcerpt();
    }

    public function testSetExcerpt(): void
    {
        $message = new ModifyExcerptMessage(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            [
                'title' => 'title-1',
                'more' => 'more-1',
                'description' => null,
                'categories' => [1, 2, 3],
                'tags' => ['tag-1', 'tag-2'],
                'icons' => ['ids' => []],
                'images' => ['ids' => [5, 6]],
            ]
        );

        $excerpt = $this->prophesize(ExcerptViewInterface::class);

        $this->assertSame($message, $message->setExcerpt($excerpt->reveal()));
        $this->assertSame($excerpt->reveal(), $message->getExcerpt());
    }
}
