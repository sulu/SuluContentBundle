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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

interface ContentWorkflowInterface
{
    /**
     * @param mixed[] $dimensionAttributes
     */
    public function apply(
        ContentInterface $content,
        array $dimensionAttributes,
        string $transitionName
    ): ContentViewInterface;
}
