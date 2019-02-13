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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Message;

use Sulu\Bundle\ContentBundle\Model\Content\Message\DuplicateContentMessage;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\ContentDimensionTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionIdentifierTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class DuplicateContentMessageTest extends SuluTestCase
{
    use ContentDimensionTrait;
    use DimensionIdentifierTrait;

    public function testDuplicate(): void
    {
        $contentEN = $this->createDraftContentDimension(
            'test_resource_contents',
            'test-resource-1',
            'en',
            'default',
            ['title' => 'Sulu', 'article' => 'Sulu is awesome']
        );

        $contentDE = $this->createDraftContentDimension(
            'test_resource_contents',
            'test-resource-1',
            'de',
            'default',
            ['title' => 'Sulu', 'article' => 'Sulu is awesome']
        );

        $message = new DuplicateContentMessage('test_resource_contents', 'test-resource-1');
        $this->getMessageBus()->dispatch($message);

        $this->assertNotSame('test-resource-1', $message->getNewResourceId());

        $newContentEN = $this->findDraftContentDimension(
            'test_resource_contents',
            $message->getNewResourceId(),
            'en'
        );
        $newContentDE = $this->findDraftContentDimension(
            'test_resource_contents',
            $message->getNewResourceId(),
            'de'
        );

        $this->assertNotNull($newContentEN);
        $this->assertNotNull($newContentDE);

        $this->assertSame($contentEN->getResourceKey(), $newContentEN->getResourceKey());
        $this->assertSame($contentDE->getResourceKey(), $newContentDE->getResourceKey());

        $this->assertSame($contentEN->getDimensionIdentifier(), $newContentEN->getDimensionIdentifier());
        $this->assertSame($contentDE->getDimensionIdentifier(), $newContentDE->getDimensionIdentifier());

        $this->assertSame($contentEN->getType(), $newContentEN->getType());
        $this->assertSame($contentDE->getType(), $newContentDE->getType());

        $this->assertSame($contentEN->getData(), $newContentEN->getData());
        $this->assertSame($contentDE->getData(), $newContentDE->getData());
    }
}
