<?php

declare(strict_types=1);

namespace Rlnks\Models;

use DateTimeImmutable;

/**
 * Tree Variable model.
 *
 * Variables allow dynamic values in decision trees that can be updated
 * via API or webhook without modifying the tree structure.
 *
 * @property string $key Variable key (identifier)
 * @property string|null $value Variable value as string
 * @property mixed $typed_value Variable value cast to its type
 * @property string $type Variable type ('string', 'number', 'boolean')
 * @property string|null $description Variable description
 * @property string $webhook_url URL to update this variable via webhook
 * @property string $created_at Creation timestamp
 * @property string $updated_at Last update timestamp
 */
class TreeVariable extends Model
{
    /**
     * Check if variable is a string type.
     */
    public function isString(): bool
    {
        return $this->type === 'string';
    }

    /**
     * Check if variable is a number type.
     */
    public function isNumber(): bool
    {
        return $this->type === 'number';
    }

    /**
     * Check if variable is a boolean type.
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * Get the typed value.
     */
    public function getValue(): mixed
    {
        return $this->typed_value ?? $this->value;
    }

    /**
     * Get value as boolean.
     */
    public function asBoolean(): bool
    {
        $value = $this->value;

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $lower = strtolower(trim($value));
            return !in_array($lower, ['false', '0', 'no', 'n', 'non', 'off', ''], true);
        }

        return (bool) $value;
    }

    /**
     * Get value as number.
     */
    public function asNumber(): float
    {
        return (float) $this->value;
    }

    /**
     * Get value as integer.
     */
    public function asInteger(): int
    {
        return (int) $this->value;
    }

    /**
     * Get creation date as DateTimeImmutable.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at ? new DateTimeImmutable($this->created_at) : null;
    }

    /**
     * Get update date as DateTimeImmutable.
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at ? new DateTimeImmutable($this->updated_at) : null;
    }
}
