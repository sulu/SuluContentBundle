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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Infrastructure\Sulu\Search;

use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Search\ContentReindexProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\ModifyExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\PublishExampleTrait;
use Sulu\Component\HttpKernel\SuluKernel;

class ContentReindexProviderTest extends BaseTestCase
{
    use CreateExampleTrait;
    use ModifyExampleTrait;
    use PublishExampleTrait;

    /**
     * @var ContentManagerInterface
     */
    private $contentManager;

    /**
     * @var ContentReindexProvider
     */
    private $reindexProvider;

    /**
     * @var ContentRichEntityInterface
     */
    private static $example1;

    /**
     * @var ContentRichEntityInterface
     */
    private static $example2;

    public static function setUpBeforeClass(): void
    {
        static::purgeDatabase();
        parent::setUpBeforeClass();

        static::$example1 = static::createExample(['title' => 'example-1'], 'en')->getResource();
        static::modifyExample(static::$example1->getId(), ['title' => 'beispiel-1'], 'de');
        static::publishExample(static::$example1->getId(), 'de');

        static::$example2 = static::createExample(['title' => 'example-2'], 'en')->getResource();
    }

    protected function setUp(): void
    {
        $this->contentManager = $this->getContainer()->get('sulu_content.content_manager');
        $this->reindexProvider = $this->getContainer()->get('example_test.example_reindex_provider');
    }

    public function testProvide(): void
    {
        $examples = $this->reindexProvider->provide(ExampleDimensionContent::class, 0, 10);

        $this->assertContains(static::$example1, $examples);
        $this->assertContains(static::$example2, $examples);
        $this->assertCount(2, $examples);
    }

    public function testGetCount(): void
    {
        $count = $this->reindexProvider->getCount(ExampleDimensionContent::class);

        $this->assertSame(2, (int) $count);
    }

    public function testCleanUp(): void
    {
        $this->expectNotToPerformAssertions();

        $this->reindexProvider->cleanUp(ExampleDimensionContent::class);
    }

    public function testGetClassFqns(): void
    {
        $this->assertSame(
            [ExampleDimensionContent::class],
            $classFqns = $this->reindexProvider->getClassFqns()
        );
    }

    public function testGetLocalesForObject(): void
    {
        $locales = $this->reindexProvider->getLocalesForObject(static::$example1);

        $this->assertSame(['en', 'de'], $locales);
    }

    public function testGetLocalesForObjectNoContentRichEntity(): void
    {
        $locales = $this->reindexProvider->getLocalesForObject(new \stdClass());

        $this->assertSame([], $locales);
    }

    public function testGetLocalesForObjectWebsite(): void
    {
        $reflection = new \ReflectionClass($this->reindexProvider);
        $property = $reflection->getProperty('context');
        $property->setAccessible(true);
        $property->setValue($this->reindexProvider, SuluKernel::CONTEXT_WEBSITE);

        $locales = $this->reindexProvider->getLocalesForObject(static::$example1);

        $this->assertSame(['de'], $locales);
    }

    public function testTranslateObject(): void
    {
        $dimensionContent = $this->contentManager->resolve(static::$example1, [
            'stage' => DimensionInterface::STAGE_DRAFT,
            'locale' => 'en',
        ]);

        $translatedObject = $this->reindexProvider->translateObject(static::$example1, 'en');

        $this->assertInstanceOf(DimensionContentInterface::class, $translatedObject);
        $this->assertSame(
            $dimensionContent->getDimension(),
            $translatedObject->getDimension()
        );
    }

    public function testTranslateObjectWebsite(): void
    {
        $reflection = new \ReflectionClass($this->reindexProvider);
        $property = $reflection->getProperty('context');
        $property->setAccessible(true);
        $property->setValue($this->reindexProvider, SuluKernel::CONTEXT_WEBSITE);

        $dimensionContent = $this->contentManager->resolve(static::$example1, [
            'stage' => DimensionInterface::STAGE_LIVE,
            'locale' => 'de',
        ]);

        $translatedObject = $this->reindexProvider->translateObject(static::$example1, 'de');

        $this->assertInstanceOf(DimensionContentInterface::class, $translatedObject);
        $this->assertSame(
            $dimensionContent->getDimension(),
            $translatedObject->getDimension()
        );
    }

    public function testTranslateObjectWebsiteNotPublished(): void
    {
        $reflection = new \ReflectionClass($this->reindexProvider);
        $property = $reflection->getProperty('context');
        $property->setAccessible(true);
        $property->setValue($this->reindexProvider, SuluKernel::CONTEXT_WEBSITE);

        $this->assertNull(
            $this->reindexProvider->translateObject(static::$example1, 'en')
        );
    }

    public function testTranslateObjectNoContentRichEntity(): void
    {
        $object = new \stdClass();

        $this->assertSame($object, $this->reindexProvider->translateObject($object, 'en'));
    }
}
