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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\EventSubscriber;

use JMS\Serializer\SerializationContext;
use Sulu\Bundle\ContentBundle\Model\Content\ContentView;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ContentViewSerializationSubscriberTest extends SuluTestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testSerializeToJson(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            '123-123-123',
            'de',
            'default',
            ['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>']
        );

        $result = $this->getContainer()->get('jms_serializer')->serialize(
            $contentView,
            'json',
            SerializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame(
            [
                'id' => '123-123-123',
                'template' => 'default',
                'title' => 'Sulu',
                'article' => '<p>Sulu is awesome</p>',
            ],
            json_decode($result, true)
        );
    }

    public function testSerializeToJsonMissingField(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            '123-123-123',
            'de',
            'default',
            ['title' => 'Sulu']
        );

        $result = $this->getContainer()->get('jms_serializer')->serialize(
            $contentView,
            'json',
            SerializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame(
            [
                'id' => '123-123-123',
                'template' => 'default',
                'title' => 'Sulu',
                'article' => null,
            ],
            json_decode($result, true)
        );
    }
}
