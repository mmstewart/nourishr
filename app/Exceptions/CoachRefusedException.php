<?php

namespace App\Exceptions;

/**
 * Thrown when the model provider blocks a question for safety reasons.
 * The user sees a "can't help with that" message instead of "unavailable".
 */
class CoachRefusedException extends CoachUnavailableException
{
}
