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
        $example1en = static::createExample([
            'title' => 'example-1',
            'article' => 'example-1-article',
            'excerptTitle' => 'example-1-excerpt-title',
            'excerptDescription' => 'example-1-excerpt-description',
        ], 'en');
        $example1en = static::publishExample($example1en->getContentId(), 'en');

        $example1de = static::modifyExample($example1en->getContentId(), [
            'title' => 'beispiel-1',
            'article' => null,
            'excerptDescription' => 'beispiel-1-excerpt-description',
        ], 'de');
        $example1de = static::publishExample($example1de->getContentId(), 'de');

        static::$exampleIds[] = $example1en->getContentId();

        // Example 2 (only en, published)
        $example2en = static::createExample(['title' => 'example-2'], 'en');
        $example2en = static::publishExample($example2en->getContentId(), 'en');

        static::$exampleIds[] = $example2en->getContentId();

        // Example 3 (both locales, only en published)
        $example3en = static::createExample(['title' => 'example-3'], 'en');
        $example3en = static::publishExample($example3en->getContentId(), 'en');

        $example3de = static::modifyExample($example3en->getContentId(), ['title' => 'beispiel-3'], 'de');

        static::$exampleIds[] = $example3en->getContentId();

        // Example 4 (only de, published)
        $example4de = static::createExample(['title' => 'beispiel-4'], 'de');
        $example4de = static::publishExample($example4de->getContentId(), 'de');

        static::$exampleIds[] = $example4de->getContentId();

        // Example 5 (only en, not published)
        $example5en = static::createExample(['title' => 'example-5'], 'en');

        static::$exampleIds[] = $example5en->getContentId();
    }

    public function setUp(): void
    {
        $this->exampleTeaserProvider = $this->getContainer()->get('example_test.example_teaser_provider');
    }

    public function testFindDE(): void
    {
        $teasers = $this->exampleTeaserProvider->find(static::$exampleIds, 'de');

        $teasers = $this->mapTeasers($teasers);

        $this->assertContent('teasers_de.json', json_encode($teasers) ?: '');
    }

    public function testFindEN(): void
    {
        $teasers = $this->exampleTeaserProvider->find(static::$exampleIds, 'en');

        $teasers = $this->mapTeasers($teasers);

        $this->assertContent('teasers_en.json', json_encode($teasers) ?: '');
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

    protected function getResponseContentFolder(): string
    {
        return 'snapshots';
    }
}
