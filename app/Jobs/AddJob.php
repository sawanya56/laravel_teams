<?php

namespace App\Jobs;

use App\Http\Controllers\AddDropController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $class_id;
   

    public function __construct($class_id, )
    {
        //
        $this->class_id = $class_id;
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $job = new AddDropController();
        $job->addStudentFromTeamByAdds($this->class_id);

    }
}
