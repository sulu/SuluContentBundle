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

namespace Sulu\Bundle\ContentBundle\Content\Application\Message;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

class WorkflowTransitionContentMessage
{
    /**
     * @var ContentInterface
     */
    private $content;

    /**
     * @var mixed[]
     */
    private $dimensionAttributes;

    /**
     * @var string
     */
    private $workflowStage;

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function __construct(ContentInterface $content, array $dimensionAttributes, string $workflowStage)
    {
        $this->content = $content;
        $this->dimensionAttributes = $dimensionAttributes;
        $this->workflowStage = $workflowStage;
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }

    /**
     * @return mixed[]
     */
    public function getDimensionAttributes(): array
    {
        return $this->dimensionAttributes;
    }

    public function getWorkflowStage(): string
    {
        return $this->workflowStage;
    }
}
