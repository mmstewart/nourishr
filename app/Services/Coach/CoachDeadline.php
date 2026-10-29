<?php

namespace App\Services\Coach;

use App\Exceptions\CoachUnavailableException;

/**
 * One question can take several model calls once tools are involved. This
 * keeps their total time under COACH_TOTAL_TIMEOUT so the request finishes
 * before the hosting platform cuts it off.
 */
class CoachDeadline
{
    /** Don't start a call with less time than this left. */
    public const MIN_SECONDS_PER_CALL = 3;

    private float $endsAt;

    public function __construct(?int $totalSeconds = null)
    {
        $this->endsAt = microtime(true) + ($totalSeconds ?? (int) config('services.coach.total_timeout'));
    }

    public function remaining(): float
    {
        return $this->endsAt - microtime(true);
    }

    /**
     * Timeout for the next HTTP call: the per-call limit, or whatever is left
     * of the total budget if that's less.
     *
     * @throws CoachUnavailableException when there isn't enough time for another call
     */
    public function timeoutForNextCall(): int
    {
        $remaining = (int) floor($this->remaining());

        if ($remaining < self::MIN_SECONDS_PER_CALL) {
            throw new CoachUnavailableException('The coach ran out of time for this question.');
        }

        return min((int) config('services.coach.timeout'), $remaining);
    }
}
