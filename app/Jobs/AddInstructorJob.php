<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddInstructorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $class_id;
    private $team_id;

    public function __construct($class_id, $team_id)
    {
        $this->class_id = $class_id;
        $this->team_id = $team_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new MsController();
        $job->AddInstructor($this->class_id, $this->team_id);
    }
}
