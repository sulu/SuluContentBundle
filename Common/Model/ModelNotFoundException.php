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

abstract class ModelNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var array
     */
    private $criteria;

    public function __construct(string $model, array $criteria, $code = 0, \Throwable $previous = null)
    {
        $criteriaMessages = [];
        foreach ($criteria as $key => $value) {
            $criteriaMessages[] = sprintf('with %s "%s"', $key, $value);
        }

        $message = sprintf(
            'Model "%s" with %s not found',
            $model,
            implode(' and ', $criteriaMessages)
        );

        parent::__construct($message, $code, $previous);

        $this->model = $model;
        $this->criteria = $criteria;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
