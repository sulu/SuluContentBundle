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

namespace Sulu\Bundle\ContentBundle\Common\Payload;

use Webmozart\Assert\Assert;

trait PayloadTrait
{
    /**
     * @var array
     */
    protected $payload;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function keyExists(string $key): bool
    {
        return \array_key_exists($key, $this->payload);
    }

    public function getValue(string $key)
    {
        Assert::keyExists($this->payload, $key);

        return $this->payload[$key];
    }

    public function getBoolValue(string $key): bool
    {
        $value = $this->getValue($key);

        Assert::boolean($value);

        return $value;
    }

    public function getNullableBoolValue(string $key): ?bool
    {
        $value = $this->getValue($key);
        if (null === $value) {
            return null;
        }

        Assert::boolean($value);

        return $value;
    }

    public function getStringValue(string $key): string
    {
        $value = $this->getValue($key);

        Assert::string($value);

        return $value;
    }

    public function getNullableStringValue(string $key): ?string
    {
        $value = $this->getValue($key);
        if (null === $value) {
            return null;
        }

        Assert::string($value);

        return $value;
    }

    public function getDateTimeValueValue(string $key): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->getStringValue($key));
    }

    public function getNullableDateTimeValue(string $key): ?\DateTimeImmutable
    {
        $value = $this->getNullableStringValue($key);
        if (!$value) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }

    public function getFloatValue(string $key): float
    {
        $value = $this->getValue($key);

        if (\is_int($value)) {
            $value = (float) $value;
        }

        Assert::float($value);

        return $value;
    }

    public function getNullableFloatValue(string $key): ?float
    {
        $value = $this->getValue($key);
        if (null === $value) {
            return null;
        }

        Assert::float($value);

        return $value;
    }

    public function getIntValue(string $key): int
    {
        $value = $this->getValue($key);

        Assert::integer($value);

        return $value;
    }

    public function getNullableIntValue(string $key): ?int
    {
        $value = $this->getValue($key);
        if (null === $value) {
            return null;
        }

        Assert::integer($value);

        return $value;
    }

    public function getArrayValue(string $key): array
    {
        $value = $this->getValue($key);

        Assert::isArray($value);

        return $value;
    }

    public function getNullableArrayValue(string $key): ?array
    {
        $value = $this->getValue($key);
        if (null === $value) {
            return null;
        }

        Assert::isArray($value);

        return $value;
    }
}
