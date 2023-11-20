<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use App\Http\Controllers\TeamController;
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
    private $team_id;
    private $student_code;
    public function __construct($class_id, $team_id,$student_code)
    {
        $this->class_id = $class_id;
        $this->team_id = $team_id;
        $this->student_code = $student_code;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new TeamController();
        $job->addStudent($this->class_id, $this->team_id, $this->student_code);
    }
}
