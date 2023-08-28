<?php

namespace App\Console\Commands;

use App\Jobs\DummyJob;
use Illuminate\Console\Command;

class QueuePlentyOfJobs extends Command
{
    protected $signature = 'app:queue-plenty-of-jobs';

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
