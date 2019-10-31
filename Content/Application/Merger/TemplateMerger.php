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

namespace Sulu\Bundle\ContentBundle\Content\Application\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateMerger extends AbstractMerger
{
    protected function supports(ContentDimensionInterface $object): bool
    {
        return $object instanceof TemplateInterface;
    }

    protected function getKey(ContentDimensionInterface $object): string
    {
        return 'template';
    }

    /**
     * @param TemplateInterface $object
     */
    protected function mergeData($object, array $data): array
    {
        foreach ($object->templateToArray() as $key => $value) {
            if ('templateData' === $key && array_key_exists($key, $data)) {
                foreach ($value as $dataKey => $dataValue) {
                    if (!array_key_exists($dataKey, $data[$key]) || !empty($dataValue)) {
                        $data[$key][$dataKey] = $dataValue;
                    }
                }

                continue;
            }

            if (!array_key_exists($key, $data) || !empty($value)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
