<?php

namespace App\Jobs;

use App\Http\Controllers\TeamController;
use App\Models\MjuClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveMeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $team_id;
    private $student_mail;
    private $class_id;
    /**
     * Create a new job instance.
     */
    public function __construct($team_id,$mail,$class_id)
    {
        $this->team_id = $team_id;
        $this->student_mail = $mail;
        $this->class_id = $class_id;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new TeamController();
        $job->removeMe($this->team_id,$this->student_mail,$this->class_id);
    }
}
