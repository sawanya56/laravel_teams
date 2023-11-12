<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use App\Http\Controllers\TeamController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $team_name;
    private $class_id;
    private $description;

    public function __construct($team_name, $class_id, $description)
    {
        $this->team_name = $team_name;
        $this->class_id = $class_id;
        $this->description = $description;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new MsController();
        $job->CreateTeams($this->team_name, $this->class_id, $this->description);
    }
}
