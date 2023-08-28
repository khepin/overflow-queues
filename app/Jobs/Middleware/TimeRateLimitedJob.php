
<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

interface TimeRateLimitedJob
{
    public function onRateLimited();
}
