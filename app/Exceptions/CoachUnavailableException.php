<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the AI coach can't produce an answer (missing key, API error,
 * timeout, or an empty response). The message is safe to log, never to show.
 */
class CoachUnavailableException extends RuntimeException
{
}
