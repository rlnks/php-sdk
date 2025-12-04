<?php

declare(strict_types=1);

namespace Rlnks\Exceptions;

/**
 * Exception thrown when rate limit is exceeded.
 */
class RateLimitException extends RlnksException
{
    protected ?int $retryAfter = null;
    protected ?int $limit = null;
    protected ?int $remaining = null;

    public function setRateLimitInfo(?int $limit, ?int $remaining, ?int $retryAfter): self
    {
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->retryAfter = $retryAfter;
        return $this;
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Get the rate limit cap.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Get remaining requests in the current window.
     */
    public function getRemaining(): ?int
    {
        return $this->remaining;
    }
}
