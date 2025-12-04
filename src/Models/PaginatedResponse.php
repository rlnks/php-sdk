<?php

declare(strict_types=1);

namespace Rlnks\Models;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Paginated response wrapper.
 *
 * @template T of Model
 * @implements IteratorAggregate<int, T>
 */
class PaginatedResponse implements IteratorAggregate, Countable
{
    /** @var T[] */
    protected array $items;
    protected int $currentPage;
    protected int $totalPages;
    protected int $totalCount;
    protected int $perPage;

    /**
     * @param T[] $items
     */
    public function __construct(
        array $items,
        int $currentPage,
        int $totalPages,
        int $totalCount,
        int $perPage
    ) {
        $this->items = $items;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->totalCount = $totalCount;
        $this->perPage = $perPage;
    }

    /**
     * Create from API response.
     *
     * @param class-string<T> $modelClass
     * @return static<T>
     */
    public static function fromResponse(array $response, string $modelClass): self
    {
        $meta = $response['meta'] ?? [];
        $data = $response['data'] ?? [];

        $items = array_map(
            fn (array $item) => $modelClass::fromResponse($item),
            $data
        );

        return new self(
            $items,
            $meta['current_page'] ?? 1,
            $meta['total_pages'] ?? 1,
            $meta['total_count'] ?? count($items),
            $meta['per_page'] ?? 20
        );
    }

    /**
     * Get all items.
     *
     * @return T[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get first item.
     *
     * @return T|null
     */
    public function first(): ?Model
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get last item.
     *
     * @return T|null
     */
    public function last(): ?Model
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * Check if there are items.
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * Check if there are items.
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Get current page number.
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get total number of pages.
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Get total number of items across all pages.
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * Get items per page.
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Check if there's a next page.
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Check if there's a previous page.
     */
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Check if on first page.
     */
    public function isFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    /**
     * Check if on last page.
     */
    public function isLastPage(): bool
    {
        return $this->currentPage >= $this->totalPages;
    }

    /**
     * Get iterator for items.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count items on current page.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(fn (Model $item) => $item->toArray(), $this->items),
            'meta' => [
                'current_page' => $this->currentPage,
                'total_pages' => $this->totalPages,
                'total_count' => $this->totalCount,
                'per_page' => $this->perPage,
            ],
        ];
    }
}
