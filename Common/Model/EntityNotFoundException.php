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

namespace Sulu\Bundle\ContentBundle\Common\Model;

class EntityNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $entity;

    /**
     * @var array
     */
    private $criteria;

    /**
     * @param mixed[] $criteria
     */
    public function __construct(string $entity, array $criteria, $code = 0, \Throwable $previous = null)
    {
        $criteriaMessages = [];
        foreach ($criteria as $key => $value) {
            $criteriaMessages[] = sprintf('with %s "%s"', $key, $value);
        }

        $message = sprintf(
            'Entity "%s" with %s not found',
            $entity,
            implode(' and ', $criteriaMessages)
        );

        parent::__construct($message, $code, $previous);

        $this->entity = $entity;
        $this->criteria = $criteria;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return mixed[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
