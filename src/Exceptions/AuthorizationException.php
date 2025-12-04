<?php

declare(strict_types=1);

namespace Rlnks\Exceptions;

/**
 * Exception thrown when authorization fails.
 *
 * This can happen when:
 * - API key lacks required scopes
 * - IP address not in whitelist
 * - Accessing resource belonging to another user/organization
 */
class AuthorizationException extends RlnksException
{
}
