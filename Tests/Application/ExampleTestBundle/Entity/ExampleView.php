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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentView;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;

class ExampleView extends AbstractContentView implements SeoInterface, ExcerptInterface, TemplateInterface
{
    use SeoTrait;
    use ExcerptTrait;
    use TemplateTrait;

    /**
     * @var Example
     */
    protected $example;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $dimensionId;

    public function __construct(Example $example, string $dimensionId)
    {
        $this->example = $example;
        $this->dimensionId = $dimensionId;
    }

    public function getContentId(): int
    {
        return $this->example->getId();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
