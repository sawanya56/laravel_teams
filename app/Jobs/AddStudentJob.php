<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddStudentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $class_id;
    private $teams_id;
    public function __construct($class_id, $teams_id)
    {
        $this->class_id = $class_id;
        $this->teams_id = $teams_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new MsController();
        $job->AddStudent($this->class_id, $this->teams_id);
    }
}
