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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;

class ExampleContentProjection implements ContentProjectionInterface, SeoInterface, ExcerptInterface, TemplateInterface, WorkflowInterface
{
    use ContentProjectionTrait;
    use SeoTrait;
    use ExcerptTrait;
    use TemplateTrait;
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

    public function getContentId()
    {
        return $this->example->getId();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public static function getTemplateType(): string
    {
        return Example::TEMPLATE_TYPE;
    }
}
