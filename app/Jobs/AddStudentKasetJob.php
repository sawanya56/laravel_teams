<?php

namespace App\Jobs;

use App\Http\Controllers\MainController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddStudentKasetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    private $class_id;
    private $team_id;
    private $student_mail;

    public function __construct($class_id, $team_id, $student_mail)
    {
        $this->class_id = $class_id;
        $this->team_id = $team_id;
        $this->student_mail = $student_mail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $job = new MainController;
        $job->addStudentKasetToTeam($this->class_id, $this->team_id, $this->student_mail);
    }
}
