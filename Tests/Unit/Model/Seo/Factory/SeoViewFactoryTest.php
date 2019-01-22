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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\Factory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactory;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;

class SeoViewFactoryTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testCreate(): void
    {
        $factory = new SeoViewFactory();

        $seoDimension1 = $this->prophesize(SeoDimensionInterface::class);
        $seoDimension1->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $seoDimension1->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $seoDimension1->getTitle()->shouldBeCalled()->willReturn('title-1');
        $seoDimension1->getDescription()->shouldBeCalled()->willReturn('description-1');
        $seoDimension1->getKeywords()->shouldBeCalled()->willReturn('keywords-1');
        $seoDimension1->getCanonicalUrl()->shouldBeCalled()->willReturn(null);
        $seoDimension1->getNoIndex()->shouldBeCalled()->willReturn(true);
        $seoDimension1->getNoFollow()->shouldBeCalled()->willReturn(null);
        $seoDimension1->getHideInSitemap()->shouldBeCalled()->willReturn(null);

        $seoDimension2 = $this->prophesize(SeoDimensionInterface::class);
        $seoDimension2->getResourceKey()->shouldNotBeCalled();
        $seoDimension2->getResourceId()->shouldNotBeCalled();
        $seoDimension2->getTitle()->shouldBeCalled()->willReturn(null);
        $seoDimension2->getDescription()->shouldBeCalled()->willReturn(null);
        $seoDimension2->getKeywords()->shouldBeCalled()->willReturn('keywords-2');
        $seoDimension2->getCanonicalUrl()->shouldBeCalled()->willReturn(null);
        $seoDimension2->getNoIndex()->shouldBeCalled()->willReturn(null);
        $seoDimension2->getNoFollow()->shouldBeCalled()->willReturn(true);
        $seoDimension2->getHideInSitemap()->shouldBeCalled()->willReturn(null);

        $result = $factory->create([$seoDimension1->reveal(), $seoDimension2->reveal()], 'en');

        $this->assertNotNull($result);
        $this->assertSame(self::RESOURCE_KEY, $result->getResourceKey());
        $this->assertSame('resource-1', $result->getResourceId());
        $this->assertSame('title-1', $result->getTitle());
        $this->assertSame('description-1', $result->getDescription());
        $this->assertSame('keywords-2', $result->getKeywords());
        $this->assertNull($result->getCanonicalUrl());
        $this->assertTrue($result->getNoIndex());
        $this->assertTrue($result->getNoFollow());
        $this->assertFalse($result->getHideInSitemap());
    }

    public function testCreateNull(): void
    {
        $factory = new SeoViewFactory();

        $this->assertNull($factory->create([], 'en'));
    }
}
