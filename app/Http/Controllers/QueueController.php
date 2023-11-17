<?php

namespace App\Http\Controllers;

use App\Jobs\AddInstructorJob;
use App\Jobs\AddStudentJob;
use App\Jobs\CreateEventJob;
use App\Jobs\CreateTeam;
use App\Jobs\GetGroupMailAndChannelIdJob;
use App\Jobs\PostMessageToTeam;
use App\Jobs\RemoveMeJob;
use App\Jobs\RemoveOwnerJob;
use App\Models\Instructor;
use App\Models\MjuClass;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class QueueController extends Controller
{
    public function __construct()
    {
        set_time_limit(3000);
    }
    public function processQueueCreateTeam()
    {
        $all_class = MjuClass::whereNull('team_id')->groupBy('class_id')->get();
        foreach ($all_class as $class) {
            try {
                $team_name = $class->team_name;
                $class_id = $class->class_id;
                $description = $class?->getCourse?->description;

                if ($description == null) {
                    $description = 'Not Description';
                }

                CreateTeam::dispatch($team_name, $class_id, $description);

            } catch (Exception $e) {
                dd($class, $class->getCourse);
                echo $e->getMessage();
                echo $class_id . "<br>";
            }

            // $this->createTeams($team_name, $section_id, $description);
        }
    }

    public function processQueueAddInstructor()
    {
        // $model = new MjuClass();
        // $all_class = $model->getClassInstructorNull();
        // $all_class = DB::table('view_ins')->get();
        $instructors = new Instructor();
        $teachers = $instructors->getInstructorClass();
        // dd($teachers->count());
        foreach ($teachers as $ins) {
            $class_id = $ins->class_id;
            $team_id = $ins?->getClassDetail?->team_id;
            $email = $ins->email;

            if ($team_id != null && $email != null) {
                AddInstructorJob::dispatch($class_id, $team_id, $email);
                // echo "" . $class_id . "" . $team_id;
            }

        }
    }

    public function processQueueAddStudent()
    {

        $getClass = new MjuClass();
        $class = $getClass->getAllClassGroupBy();

        foreach ($class as $row) {
            $team_id = $row->team_id;
            if (count($row->getEnrollment) != 0) {
                // dd($row->getEnrollment);

                foreach ($row->getEnrollment as $student) {
                    $class_id = $student->class_id;
                    $student_code = $student->student_code;

                    if ($student_code != null) {
                        // dispatch(new AddStudentJob($class_id, $team_id, $student_code));
                        AddStudentJob::dispatch($class_id, $team_id, $student_code);
                    }

                }

            }

        }

    }

    public function processQueueCreateEvent()
    {
        $all_class = DB::table('class')->select('class_id', 'team_id')
            ->whereNotNull('team_id')
            ->whereNull('add_event')
            ->groupBy('class_id')
            ->get();
        // dd($all_class);
        foreach ($all_class as $class) {
            dispatch(new CreateEventJob($class->class_id));
        }
    }

    public function processQueuePostMessageToTeam()
    {
        $all_class = DB::table('class')->where('created_at', 'like', '2023-07-03%')->groupBy('class_id')->get();

        foreach ($all_class as $class) {
            $team_id = $class->team_id;
            $class_id = $class->class_id;
            dispatch(new PostMessageToTeam($team_id, $class_id));
        }
    }

    public function getGroupMailAndChannelId()
    {
        $all_class = DB::table('class')->whereNull('group_mail')->groupBy('class_id')->get();
        foreach ($all_class as $class) {
            dispatch(new GetGroupMailAndChannelIdJob($class->class_id, $class->team_id));
        }
    }

    public function deleteAllEvent($team_id)
    {
        $events = DB::table('class')->whereNotNull('event_id')->get();
        dd($events);
        foreach ($events as $event) {

            $token = env('TOKEN');
            $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id . "/events/" . $event->event_id;

            $response = Http::withToken($token)->delete($endpoint);
            $json = $response->json();

            if (isset($json['error'])) {
                echo $event->event_id . "<br><br>";
            } else {
                dd($json);
            }
        }
    }

    public function testNun()
    {
        $instructors = new Instructor();
        $teachers = $instructors->getInstructorClass();

        foreach ($teachers as $ins) {
            $team_id = $ins->getClassDetail->team_id;
            dd($ins->getClassDetail);

        }

    }

    public function removeMeAllTeam()
    {
        $class = MjuClass::groupBy('class_id')->get();
        foreach ($class as $row) {
            $team_id = $row->team_id;
            $student_mail = 'sawanya_kck@mju.ac.th';
            $class_id = $row->class_id;
            RemoveOwnerJob::dispatch($team_id, $student_mail, $class_id);
        }
    }

    public function removeMeAll()
    {
        $class = MjuClass::groupBy('class_id')->get();
        // $class = MjuClass::where('class_id','=','337970')->get();
        foreach ($class as $row) {
            $team_id = $row->team_id;
            $student_mail = 'sawanya_kck@mju.ac.th';
            $class_id = $row->class_id;
            RemoveMeJob::dispatch($team_id, $student_mail, $class_id);
        }
    }
}
