<?php

namespace App\Console\Commands;

use App\Jobs\DummyJob;
use Illuminate\Console\Command;

class QueuePlentyOfJobs extends Command
{
    protected $signature = 'app:queue-plenty-of-jobs';

    protected $description = 'Queues a bunch of jobs for a "big user" that will take long to execute
    and a bunch of jobs for smaller users with random ids.';

    public function handle()
    {
        for ($i = 0; $i < 100; $i++) {
            dispatch(new DummyJob('biguser'));
        }
        for ($i = 0; $i < 100; $i++) {
            dispatch(new DummyJob(uniqid()));
        }
    }
}
