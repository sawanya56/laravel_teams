<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use App\Http\Controllers\TeamController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostMessageToTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $team_id;
    private $class_id;
    public function __construct($team_id, $class_id)
    {
        $this->team_id = $team_id;
        $this->class_id = $class_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new TeamController();
        $job->postMeetingToTeam($this->team_id, $this->class_id);
    }
}
