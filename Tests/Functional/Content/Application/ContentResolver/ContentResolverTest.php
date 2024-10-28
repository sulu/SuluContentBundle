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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Application\ContentResolver;

use Sulu\Bundle\ContentBundle\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CreateCategoryTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CreateMediaTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CreateTagTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateExampleTrait;
use Sulu\Bundle\MediaBundle\Api\Media;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ContentResolverTest extends SuluTestCase
{
    use CreateCategoryTrait;
    use CreateTagTrait;
    use CreateExampleTrait;
    use CreateMediaTrait;

    private ContentResolverInterface $contentResolver;
    private ContentAggregatorInterface $contentAggregator;

    protected function setUp(): void
    {
        self::purgeDatabase();
        self::initPhpcr();

        $this->contentResolver = self::getContainer()->get('sulu_content.content_resolver');
        $this->contentAggregator = self::getContainer()->get('sulu_content.content_aggregator');
    }

    public function testResolveContent(): void
    {
        $category1 = self::createCategory(['key' => 'category-1']);
        $category2 = self::createCategory(['key' => 'category-2']);
        $tag1 = self::createTag(['name' => 'tag-1']);
        $collection1 = self::createCollection(['title' => 'collection-1', 'locale' => 'en']);
        $mediaType = self::createMediaType(['name' => 'Image', 'description' => 'This is an image']);
        $media1 = self::createMedia($collection1, $mediaType, ['title' => 'media-1', 'locale' => 'en']);
        $media2 = self::createMedia($collection1, $mediaType, ['title' => 'media-2', 'locale' => 'en']);

        self::getEntityManager()->flush();

        $example1 = static::createExample(
            [
                'en' => [
                    'live' => [
                        'template' => 'full-content',
                        'title' => 'Lorem Ipsum',
                        'url' => '/lorem-ipsum',
                        'text_editor' => '<p>Lorem Ipsum dolor sit amet</p>',
                        'blocks' => [
                            [
                                'type' => 'editor',
                                'text_editor' => '<p>Block Level 0: Lorem Ipsum dolor sit amet</p>',
                            ],
                            [
                                'type' => 'media',
                                'media_selection' => [
                                    'ids' => [$media1->getId()],
                                ],
                            ],
                            [
                                'type' => 'block',
                                'blocks' => [
                                    [
                                        'type' => 'editor',
                                        'text_editor' => '<p>Block Level 1: Lorem Ipsum dolor sit amet</p>',
                                    ],
                                    [
                                        'type' => 'media',
                                        'media_selection' => [
                                            'ids' => [$media2->getId()],
                                        ],
                                    ],
                                ]
                            ]
                        ],
                        'text_line' => 'Lorem Ipsum dolor sit amet',
                        'number' => 1337,
                        'phone' => '+49 123 456 789',
//                        'tag_selection' => [$tag1->getName()],
                        'single_select' => 'value-2',
                        'select' => [
                            'value-2',
                            'value-3',
                        ],
                        'checkbox' => true,
                        'color' => '#ff0000',
                        'time' => '13:37',
                        'date' => '2020-01-01',
                        'datetime' => '2020-01-01 13:37:00',
                        'email' => 'example@sulu.io',
                        'external_url' => 'https://sulu.io',
                        'category_selection' => [$category1->getId(), $category2->getId()],
                        'single_category_selection' => $category1->getId(),
                        'collection_selection' => [$collection1->getId()],
                        'single_collection_selection' => $collection1->getId(),
                        'media_selection' => [
                            'ids' => [$media1->getId(), $media2->getId()],
                            'displayOption' => 'left',
                        ],
                        'single_media_selection' => [
                            'id' => $media1->getId(),
                            'displayOption' => 'left',
                        ],
//                        'account_selection' => [
//                            $account1->getId(),
//                            $account2->getId(),
//                        ],
//                        'single_account_selection' => $account1->getId(),
//                        'contact_selection' => [
//                            $contact1->getId(),
//                            $contact2->getId(),
//                        ],
//                        'single_contact_selection' => $contact1->getId(),
//                        'contact_account_selection' => [
//                            'c'.$contact1->getId(),
//                            'a'.$account1->getId(),
//                        ],
                        'text_area' => 'Lorem Ipsum dolor sit amet',
//                        'image_map' =>  //TODO
                        'blocks2' => [
                            [
                                'type' => 'editor',
                                'text_editor' => '<p>Block2 Level 0: Lorem Ipsum dolor sit amet</p>',
                            ],
                            [
                                'type' => 'media',
                                'media_selection' => [
                                    'ids' => [$media1->getId()],
                                ],
                            ],
                            [
                                'type' => 'block',
                                'blocks' => [
                                    [
                                        'type' => 'editor',
                                        'text_editor' => '<p>Block2 Level 1: Lorem Ipsum dolor sit amet</p>',
                                    ],
                                    [
                                        'type' => 'media',
                                        'media_selection' => [
                                            'ids' => [$media2->getId()],
                                        ],
                                    ],
                                ]
                            ]
                        ],
                        'excerptTitle' => 'excerpt-title-1',
                        'excerptMore' => 'excerpt-more-1',
                        'excerptDescription' => 'excerpt-description-1',
                        'excerptCategories' => [$category1->getId()],
//                        'excerptTags' => [$tag1->getName()],
                        'excerptIcon' => [
                            'id' => $media1->getId(),
                        ],
                        'excerptImage' => [
                            'id' => $media2->getId(),
                        ],
                        'seoTitle' => 'seo-title-1',
                        'seoDescription' => 'seo-description-1',
                        'seoKeywords' => 'seo-keywords-1',
                        'seoCanonicalUrl' => 'https://sulu.io',
                        'seoNoIndex' => true,
                        'seoNoFollow' => true,
                        'seoHideInSitemap' => true,
                    ],
                ],
            ],
            [
                'create_route' => true,
            ]
        );

        static::getEntityManager()->flush();

        $dimensionContent = $this->contentAggregator->aggregate($example1, ['locale' => 'en', 'stage' => 'live']);
        /** @var mixed[] $result */
        $result = $this->contentResolver->resolve($dimensionContent);

        /** @var mixed[] $content */
        $content = $result['content'];

        self::assertSame('Lorem Ipsum', $content['title']);
        self::assertSame('/lorem-ipsum', $content['url']);
        self::assertSame('<p>Lorem Ipsum dolor sit amet</p>', $content['text_editor']);

        // block 0
        self::assertSame('editor', $content['blocks'][0]['type']);
        self::assertSame('<p>Block Level 0: Lorem Ipsum dolor sit amet</p>', $content['blocks'][0]['text_editor']);

        // block 1
        self::assertSame('media', $content['blocks'][1]['type']);
        $mediaApi1 = $content['blocks'][1]['media_selection'][0];
        self::assertInstanceOf(Media::class, $mediaApi1);
        self::assertSame($media1->getId(), $mediaApi1->getId());
        $mediaApi2 = $content['blocks'][1]['media_selection'][1];
        self::assertInstanceOf(Media::class, $mediaApi2);
        self::assertSame($media2->getId(), $mediaApi2->getId());

        // block 2
        self::assertSame('block', $content['blocks'][2]['type']);
        self::assertSame('<p>Block Level 1: Lorem Ipsum dolor sit amet</p>', $content['blocks'][2]['blocks'][0]['text_editor']);
        self::assertSame('editor', $content['blocks'][2]['blocks'][0]['type']);

        self::assertSame('media', $content['blocks'][2]['blocks'][1]['type']);
        $mediaApi1 = $content['blocks'][2]['blocks'][1]['media_selection'][0];
        self::assertInstanceOf(Media::class, $mediaApi1);
        self::assertSame($media1->getId(), $mediaApi1->getId());
        $mediaApi2 = $content['blocks'][2]['blocks'][1]['media_selection'][1];
        self::assertInstanceOf(Media::class, $mediaApi2);
        self::assertSame($media2->getId(), $mediaApi2->getId());

        self::assertSame('Lorem Ipsum dolor sit amet', $content['text_line']);
        self::assertSame(1337, $content['number']);
        self::assertSame('+49 123 456 789', $content['phone']);
        self::assertSame('value-2', $content['single_select']);
        self::assertSame(['value-2', 'value-3'], $content['select']);
        self::assertTrue($content['checkbox']);
        self::assertSame('#ff0000', $content['color']);
        self::assertSame('13:37', $content['time']);
        self::assertSame('2020-01-01', $content['date']);
        self::assertSame('2020-01-01 13:37:00', $content['datetime']);
        self::assertSame('example@sulu.io', $content['email']);
        self::assertSame('https://sulu.io', $content['external_url']);

        self::assertCount(2, $content['category_selection']);
        $contentCategory1 = $content['category_selection'][0];
        self::assertSame($category1->getId(), $contentCategory1->getId());
        $contentCategory2 = $content['category_selection'][1];
        self::assertSame($category2->getId(), $contentCategory2->getId());

        self::assertSame($category1->getId(), $content['single_category_selection']->getId());
        self::assertCount(1, $content['collection_selection']);
        $contentCollection1 = $content['collection_selection'][0];
        self::assertSame($collection1->getId(), $contentCollection1->getId());

        self::assertSame($collection1->getId(), $content['single_collection_selection']->getId());

        self::assertCount(2, $content['media_selection']);
        $contentMedia1 = $content['media_selection'][0];
        self::assertInstanceOf(Media::class, $contentMedia1);
        self::assertSame($media1->getId(), $contentMedia1->getId());

        $contentMedia2 = $content['media_selection'][1];
        self::assertInstanceOf(Media::class, $contentMedia2);
        self::assertSame($media2->getId(), $contentMedia2->getId());

        //TODO
        //account selection / contact selection / image map / blocks 2 / excerpt / seo
    }
}
