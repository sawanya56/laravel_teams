<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Models\MjuClass;
use App\Jobs\CreateTeam;


class QueueController extends Controller
{
    public function processQueueCreateTeam()
    {
        $all_class = MjuClass::whereNull('team_id')->groupBy('class_id')->get();
        foreach ($all_class as $class) {
            try {
                $team_name = $class->team_name;
                $class_id = $class->class_id;
                $description = $class->getCourse->description;
                if($description==null){
                    $description = 'Not Description';
                }
                dispatch(new CreateTeam($team_name, $class_id, $description));
                echo($description."<br>");
            } catch (Exception $e) {
                echo $e->getMessage();
                echo $class_id . "<br>";
            }

            // $this->createTeams($team_name, $section_id, $description);
        }
    }
}
