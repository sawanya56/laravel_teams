<?php

namespace App\Jobs;

use App\Http\Controllers\TeamController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveOwnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $team_id;
    private $mail;
    private $class;
    public function __construct($team_id,$mail,$class_id)
    {
        $this->team_id = $team_id;
        $this->mail = $mail;    
        $this->class = $class_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = new TeamController();
        $job->removeOwner($this->team_id,$this->mail,$this->class);
    }
}
