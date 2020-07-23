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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Infrastructure\Sulu\Teaser;

use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Teaser\ExampleTeaserProvider;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\ModifyExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\PublishExampleTrait;
use Sulu\Bundle\PageBundle\Teaser\Teaser;

class ContentTeaserProviderTest extends BaseTestCase
{
    use CreateExampleTrait;
    use ModifyExampleTrait;
    use PublishExampleTrait;

    /**
     * @var ExampleTeaserProvider
     */
    private $exampleTeaserProvider;

    /**
     * @var mixed[]
     */
    private static $exampleIds = [];

    public static function setUpBeforeClass(): void
    {
        static::purgeDatabase();
        parent::setUpBeforeClass();

        // Example 1 (both locales, both published)
        $example1 = static::createExample([
            'title' => 'example-1',
            'article' => 'example-1-article',
            'excerptTitle' => 'example-1-excerpt-title',
            'excerptDescription' => 'example-1-excerpt-description',
        ], 'en')->getResource();
        static::publishExample($example1->getId(), 'en');

        static::modifyExample($example1->getId(), [
            'title' => 'beispiel-1',
            'article' => null,
            'excerptDescription' => 'beispiel-1-excerpt-description',
        ], 'de');
        static::publishExample($example1->getId(), 'de');

        static::$exampleIds[] = $example1->getId();

        // Example 2 (only en, published)
        $example2 = static::createExample(['title' => 'example-2'], 'en')->getResource();
        static::publishExample($example2->getId(), 'en');

        static::$exampleIds[] = $example2->getId();

        // Example 3 (both locales, only en published)
        $example3 = static::createExample(['title' => 'example-3'], 'en')->getResource();
        static::publishExample($example3->getId(), 'en');

        static::modifyExample($example3->getId(), ['title' => 'beispiel-3'], 'de');

        static::$exampleIds[] = $example3->getId();

        // Example 4 (only de, published)
        $example4 = static::createExample(['title' => 'beispiel-4'], 'de')->getResource();
        static::publishExample($example4->getId(), 'de');

        static::$exampleIds[] = $example4->getId();

        // Example 5 (only en, not published)
        $example5 = static::createExample(['title' => 'example-5'], 'en')->getResource();

        static::$exampleIds[] = $example5->getId();
    }

    public function setUp(): void
    {
        $this->exampleTeaserProvider = $this->getContainer()->get('example_test.example_teaser_provider');
    }

    public function testFindDE(): void
    {
        $teasers = $this->exampleTeaserProvider->find(static::$exampleIds, 'de');

        $teasers = $this->mapTeasers($teasers);

        $this->assertArraySnapshot('teasers_de.json', $teasers);
    }

    public function testFindEN(): void
    {
        $teasers = $this->exampleTeaserProvider->find(static::$exampleIds, 'en');

        $teasers = $this->mapTeasers($teasers);

        $this->assertArraySnapshot('teasers_en.json', $teasers);
    }

    /**
     * @param Teaser[]$teasers
     *
     * @return array<string, mixed>
     */
    private function mapTeasers(array $teasers): array
    {
        return array_map(function (Teaser $teaser) {
            return [
                'id' => $teaser->getId(),
                'type' => $teaser->getType(),
                'locale' => $teaser->getLocale(),
                'url' => $teaser->getUrl(),
                'title' => $teaser->getTitle(),
                'description' => $teaser->getDescription(),
                'moreText' => $teaser->getMoreText(),
                'mediaId' => $teaser->getMediaId(),
                'attributes' => $teaser->getAttributes(),
            ];
        }, $teasers);
    }
}
