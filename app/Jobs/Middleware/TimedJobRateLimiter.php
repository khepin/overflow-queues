<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use SebastianBergmann\Timer\Timer;

class TimedJobRateLimiter
{
    protected TimedRateLimiter $rl;

    protected ?Timer $timer = null;

    public function __construct(
        protected string $key,
        protected int $maxSecondsSpent,
        protected int $periodSeconds = 60
    ) {
    }

    public function handle(TimeRateLimitedJob $job, callable $next)
    {
        $this->rl = app(TimedRateLimiter::class);

        /**
         * Our actual implementation also adds a listener here to catch when the worker is stopping.
         * This allows us to catch timeouts and still count those towards the user.
         */
        if ($this->rl->tooManyAttempts($this->key, $this->maxSecondsSpent * 1000)) {
            $job->onRateLimited();

            return;
        }

        $this->timer = new Timer();
        $this->timer->start();

        try {
            $next($job);
        } finally {
            $this->stop();
        }
    }

    /**
     * Stop the timer and increment the ratelimiter by how many milliseconds elapsed
     */
    public function stop()
    {
        if (is_null($this->timer)) {
            return;
        }
        $msElapsed = $this->timer->stop()->asMilliseconds();
        $this->rl->increment($this->key, $this->periodSeconds, $msElapsed);
        $this->timer = null;
    }
}
