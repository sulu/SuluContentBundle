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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\PropertyResolver\Resolver;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Bundle\ContentBundle\Content\Application\MetadataResolver\MetadataResolver;
use Sulu\Bundle\ContentBundle\Content\Application\PropertyResolver\PropertyResolverProvider;
use Sulu\Bundle\ContentBundle\Content\Application\PropertyResolver\Resolver\BlockPropertyResolver;
use Sulu\Bundle\ContentBundle\Content\Application\PropertyResolver\Resolver\DefaultPropertyResolver;
use Symfony\Component\ErrorHandler\BufferingLogger;

class BlockPropertyResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $propertyResolverProvider = new PropertyResolverProvider(
            new \ArrayIterator([
                'default' => new DefaultPropertyResolver(),
            ])
        );
        $metadataResolver = new MetadataResolver($propertyResolverProvider);

        $blockPropertyResolver = new BlockPropertyResolver(
            new BufferingLogger(),
            false
        );
        $blockPropertyResolver->setMetadataResolver($metadataResolver);

        $data = [
            [
                'type' => 'text_block',
                'title' => 'Sulu',
                'description' => 'Sulu is awesome',
            ],
        ];
        $locale = 'en';

        $formMetadata = new FormMetadata();
        $formMetadata->setName('text_block');
        $formMetadata->setKey('text_block');
        $blockFieldMetadata = new FieldMetadata('text_block');
        $blockFieldMetadata->addType($formMetadata);

        $tileFieldMetadata = new FieldMetadata('title');
        $tileFieldMetadata->setType('text_line');

        $descriptionFieldMetadata = new FieldMetadata('description');
        $descriptionFieldMetadata->setType('text_area');

        $formMetadata->addItem($tileFieldMetadata);
        $formMetadata->addItem($descriptionFieldMetadata);
        $params = [
            'metadata' => $blockFieldMetadata,
        ];

        $content = $blockPropertyResolver->resolve($data, $locale, $params);
        $this->assertInstanceOf(ContentView::class, $content);
        /** @var mixed[] $innerContent */
        $innerContent = $content->getContent();
        $this->assertCount(1, $innerContent);
        /** @var mixed[] $blockData */
        $blockData = $innerContent[0];
        $this->assertSame('text_block', $blockData['type']);
        $this->assertSame('Sulu', $blockData['title']->getContent());
        $this->assertSame([], $blockData['title']->getView());
        $this->assertSame('Sulu is awesome', $blockData['description']->getContent());
        $this->assertSame([], $blockData['description']->getView());

        $this->assertSame([], $content->getView());
    }

    public function testGetType(): void
    {
        $this->assertSame('block', BlockPropertyResolver::getType());
    }
}
