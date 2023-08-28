<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use Illuminate\Cache\RateLimiter;

class TimedRateLimiter extends RateLimiter
{
    /**
     * Copy of RateLimiter::hit with the only difference that we can specify how much to increment / count towards
     * the limit.
     */
    public function increment($key, $decaySeconds = 60, $howMuch = 1)
    {
        $key = $this->cleanRateLimiterKey($key);

        $this->cache->add(
            $key.':timer', $this->availableAt($decaySeconds), $decaySeconds
        );

        $added = $this->cache->add($key, 0, $decaySeconds);

        $hits = (int) $this->cache->increment($key, $howMuch);

        if (! $added && $hits == 1) {
            $this->cache->put($key, 1, $decaySeconds);
        }

        return $hits;
    }
}
