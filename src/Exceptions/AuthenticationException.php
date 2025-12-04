<?php

declare(strict_types=1);

namespace Rlnks\Exceptions;

/**
 * Exception thrown when authentication fails.
 *
 * This can happen when:
 * - API key is invalid or missing
 * - API key format is incorrect
 * - API key has been disabled
 * - Email is not verified (for user API keys)
 */
class AuthenticationException extends RlnksException
{
}
