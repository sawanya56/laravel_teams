<?php

namespace App\Jobs;

use App\Http\Controllers\MsController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteAllTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $section_id;
    private $ms_team_id;
    private $access_token;
    public function __construct($section_id, $ms_team_id,$access_token)
    {
        $this->section_id = $section_id;
        $this->ms_team_id = $ms_team_id;
        $this->access_token = $access_token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new MsController();
        $job->deleteAllGroup($this->ms_team_id,$this->section_id,$this->access_token);
    }
}
