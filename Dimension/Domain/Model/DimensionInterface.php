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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Model;

interface DimensionInterface
{
    const WORKFLOW_STAGE_DRAFT = 'draft';
    const WORKFLOW_STAGE_LIVE = 'live';

    public function getId(): string;

    public function getLocale(): ?string;

    public function getWorkflowStage(): string;

    public static function getDefaultValues();
}
