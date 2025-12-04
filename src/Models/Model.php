<?php

declare(strict_types=1);

namespace Rlnks\Models;

use ArrayAccess;
use JsonSerializable;

/**
 * Base model class for RLNKS resources.
 */
abstract class Model implements ArrayAccess, JsonSerializable
{
    protected array $attributes = [];
    protected array $original = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    /**
     * Fill the model with attributes.
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set an attribute.
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get an attribute.
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Check if attribute exists.
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Get all attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the original attributes.
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Get dirty (changed) attributes.
     */
    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    /**
     * Check if the model has changes.
     */
    public function isDirty(?string $key = null): bool
    {
        if ($key !== null) {
            return array_key_exists($key, $this->getDirty());
        }
        return count($this->getDirty()) > 0;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * JsonSerializable implementation.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Magic getter.
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic isset.
     */
    public function __isset(string $key): bool
    {
        return $this->hasAttribute($key);
    }

    // ArrayAccess implementation

    public function offsetExists(mixed $offset): bool
    {
        return $this->hasAttribute($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Create a new instance from API response data.
     */
    public static function fromResponse(array $data): static
    {
        return new static($data);
    }
}
