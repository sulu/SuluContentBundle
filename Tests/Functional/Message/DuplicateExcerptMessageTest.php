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

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\DuplicateExcerptMessage;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CategoryTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionIdentifierTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\ExcerptDimensionTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\MediaTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\TagTrait;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class DuplicateExcerptMessageTest extends SuluTestCase
{
    use DimensionIdentifierTrait;
    use ExcerptDimensionTrait;
    use CategoryTrait;
    use TagTrait;
    use MediaTrait;

    /**
     * @var CategoryInterface
     */
    private $category1;

    /**
     * @var CategoryInterface
     */
    private $category2;

    /**
     * @var TagInterface
     */
    private $tag1;

    /**
     * @var TagInterface
     */
    private $tag2;

    /**
     * @var MediaInterface
     */
    private $media1;

    /**
     * @var MediaInterface
     */
    private $media2;

    /**
     * @var MediaInterface
     */
    private $media3;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();

        $collectionType = $this->createCollectionType('collection-type-1');
        $collection = $this->createCollection($collectionType);
        $mediaTpe = $this->createMediaType('media-type-1');

        $this->category1 = $this->createCategory();
        $this->category2 = $this->createCategory();
        $this->tag1 = $this->createTag('tag-1');
        $this->tag2 = $this->createTag('tag-2');
        $this->media1 = $this->createMedia($mediaTpe, $collection);
        $this->media2 = $this->createMedia($mediaTpe, $collection);
        $this->media3 = $this->createMedia($mediaTpe, $collection);
    }

    public function testDuplicate(): void
    {
        $excerptEN = $this->createDraftExcerptDimension(
            'test_resource_excerpts',
            'test-resource-1',
            'en',
            'excerpt-title',
            'excerpt-more',
            'excerpt-description',
            [$this->category1],
            [$this->tag2, $this->tag1],
            [$this->media3, $this->media2],
            [$this->media1]
        );

        $excerptDE = $this->createDraftExcerptDimension(
            'test_resource_excerpts',
            'test-resource-1',
            'de',
            'excerpt-title',
            'excerpt-more',
            'excerpt-description',
            [$this->category1],
            [$this->tag1],
            [$this->media2],
            [$this->media1]
        );

        $message = new DuplicateExcerptMessage('test_resource_excerpts', 'test-resource-1', 'new-resource-1');
        $this->getMessageBus()->dispatch($message);

        $newExcerptEN = $this->findDraftExcerptDimension(
            'test_resource_excerpts',
            'new-resource-1',
            'en'
        );
        $newExcerptDE = $this->findDraftExcerptDimension(
            'test_resource_excerpts',
            'new-resource-1',
            'de'
        );

        $this->assertNotNull($newExcerptEN);
        $this->assertNotNull($newExcerptDE);

        $this->assertSame($excerptEN->getResourceKey(), $newExcerptEN->getResourceKey());
        $this->assertSame($excerptDE->getResourceKey(), $newExcerptDE->getResourceKey());

        $this->assertSame($excerptEN->getDimensionIdentifier(), $newExcerptEN->getDimensionIdentifier());
        $this->assertSame($excerptDE->getDimensionIdentifier(), $newExcerptDE->getDimensionIdentifier());
    }

    private function getMessageBus(): MessageBusInterface
    {
        /** @var MessageBusInterface $messageBus */
        $messageBus = $this->getContainer()->get('message_bus');

        return $messageBus;
    }
}
