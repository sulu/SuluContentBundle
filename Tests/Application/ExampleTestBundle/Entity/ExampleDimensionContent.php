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

namespace Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;

class ExampleDimensionContent implements DimensionContentInterface, ExcerptInterface, SeoInterface, TemplateInterface, RoutableInterface, WorkflowInterface
{
    use DimensionContentTrait;
    use ExcerptTrait;
    use RoutableTrait;
    use SeoTrait;
    use TemplateTrait {
        getTemplateData as parentGetTemplateData;
        setTemplateData as parentSetTemplateData;
    }
    use WorkflowTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Example
     */
    protected $example;

    /**
     * @var string|null
     */
    protected $title;

    public function __construct(Example $example, DimensionInterface $dimension)
    {
        $this->example = $example;
        $this->dimension = $dimension;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getResource(): ContentRichEntityInterface
    {
        return $this->example;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTemplateData(): array
    {
        $data = $this->parentGetTemplateData();
        $data['title'] = $this->getTitle();

        return $data;
    }

    public function setTemplateData(array $templateData): void
    {
        $this->setTitle($templateData['title']);
        unset($templateData['title']);
        $this->parentSetTemplateData($templateData);
    }

    public static function getTemplateType(): string
    {
        return Example::TEMPLATE_TYPE;
    }

    public static function getResourceKey(): string
    {
        return Example::RESOURCE_KEY;
    }
}
