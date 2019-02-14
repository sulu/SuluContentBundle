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

use Sulu\Bundle\ContentBundle\Model\Seo\Message\DuplicateSeoMessage;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionIdentifierTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\SeoDimensionTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class DuplicateSeoMessageTest extends SuluTestCase
{
    use DimensionIdentifierTrait;
    use SeoDimensionTrait;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();
    }

    public function testDuplicate(): void
    {
        $seoEN = $this->createDraftSeoDimension(
            'test_resource_seos',
            'test-resource-1',
            'en',
            'seo-title-en',
            'seo-description-en',
            'seo-keywords-en',
            'seo-url-en',
            false,
            true,
            false
        );

        $seoDE = $this->createDraftSeoDimension(
            'test_resource_seos',
            'test-resource-1',
            'de',
            'seo-title-de',
            'seo-description-de',
            'seo-keywords-de',
            'seo-url-de',
            false,
            true,
            false
        );

        $message = new DuplicateSeoMessage('test_resource_seos', 'test-resource-1', 'new-resource-1');
        $this->getMessageBus()->dispatch($message);

        $newSeoEN = $this->findDraftSeoDimension(
            'test_resource_seos',
            'new-resource-1',
            'en'
        );
        $newSeoDE = $this->findDraftSeoDimension(
            'test_resource_seos',
            'new-resource-1',
            'de'
        );

        $this->assertNotNull($newSeoEN);
        $this->assertNotNull($newSeoDE);

        $this->assertSame($seoEN->getResourceKey(), $newSeoEN->getResourceKey());
        $this->assertSame($seoDE->getResourceKey(), $newSeoDE->getResourceKey());

        $this->assertSame($seoEN->getDimensionIdentifier(), $newSeoEN->getDimensionIdentifier());
        $this->assertSame($seoDE->getDimensionIdentifier(), $newSeoDE->getDimensionIdentifier());

        $this->assertSame($seoEN->getTitle(), $newSeoEN->getTitle());
        $this->assertSame($seoDE->getTitle(), $newSeoDE->getTitle());

        $this->assertSame($seoEN->getDescription(), $newSeoEN->getDescription());
        $this->assertSame($seoDE->getDescription(), $newSeoDE->getDescription());
    }

    private function getMessageBus(): MessageBusInterface
    {
        /** @var MessageBusInterface $messageBus */
        $messageBus = $this->getContainer()->get('message_bus');

        return $messageBus;
    }
}
