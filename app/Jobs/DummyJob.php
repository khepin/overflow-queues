<?php

namespace App\Jobs;

use App\Jobs\Middleware\TimedJobRateLimiter;
use App\Jobs\Middleware\TimeRateLimitedJob;

class DummyJob extends BaseJob implements TimeRateLimitedJob
{
    /**
     * The normal queue where those jobs get executed
     */
    public const QUEUE = 'dummy.job.queue';

    /**
     * The queue where users with too many / too intensive jobs see their jobs redirected
     */
    public const OVERFLOW_QUEUE = self::QUEUE.'.overflow';

    public function __construct(protected string $userId)
    {
        $this->queue = self::QUEUE;
    }

    public function handle(): void
    {
        // The user 'biguser' has jobs that take much longer to execute than others
        if ($this->userId === 'biguser') {
            sleep(1);
        }
    }

    public function middleware()
    {
        // We only apply this middleware if we're running on the normal queue. Jobs that are already on the
        // overflow queue should not be checked.
        // Note: this check could easily be moved to the middleware instead...
        if ($this->queue === static::QUEUE) {
            return [
                new TimedJobRateLimiter(
                    key: self::class.':'.$this->userId,
                    maxSecondsSpent: 10, // If a user spends more than 10s of computing over a period of 1 minute
                    periodSeconds: 60,
                ),
            ];
        }

        return [];
    }

    /**
     * When the job hits a rate limit, what do we do? (In this case, send it to the second queue)
     */
    public function onRateLimited()
    {
        $this->overflow(self::OVERFLOW_QUEUE);
    }
}
