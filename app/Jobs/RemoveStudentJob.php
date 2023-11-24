<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use App\Http\Controllers\TeamController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveStudentJob implements ShouldQueue
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
        $job = new TeamController();
        $job->removeStudentFromTeam($this->class_id);
    }
}
