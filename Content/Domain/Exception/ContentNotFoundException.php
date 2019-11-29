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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Exception;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

class ContentNotFoundException extends \Exception
{
    public function __construct(ContentInterface $content, array $dimensionAttributes)
    {
        parent::__construct(sprintf(
            'Could not load content with id "%s" and attributes: %s',
            $content->getId(),
            json_encode($dimensionAttributes)
        ));
    }
}
