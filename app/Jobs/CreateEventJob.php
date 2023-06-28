<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $class_id;

    public function __construct($class_id)
    {
        $this->class_id = $class_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new MsController();
        $job->CreateEvent($this->class_id);
    }
}
