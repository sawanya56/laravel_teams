<?php

namespace App\Http\Controllers;

use App\Jobs\AddInstructorJob;
use App\Jobs\AddStudentJob;
use App\Jobs\CreateEventJob;
use App\Jobs\CreateTeam;
use App\Jobs\GetGroupMailAndChannelIdJob;
use App\Jobs\PostMessageToTeam;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\MjuClass;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
                if ($description == null) {
                    $description = 'Not Description';
                }
                dispatch(new CreateTeam($team_name, $class_id, $description));
                // echo($description."<br>");00000
            } catch (Exception $e) {
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
        foreach ($teachers as $ins) {
            $class_id = $ins->class_id;
            $team_id = $ins->getClassDetail->team_id;
            $email = $ins->email;
            if ($email != null) {
                dispatch(new AddInstructorJob($class_id, $team_id, $email));
            }
        }
    }

    public function processQueueAddStudent()
    {
        // dispatch(new AddStudentJob(337152, '221d70ec-bea1-485b-91b6-7f6c7d07f0da'));
        $enroll = new Enrollment();
        $students = $enroll->getStudentClass();
        foreach ($students as $student) {
            $class_id = $student->class_id;
            $team_id = $student->getClass->team_id;
            $student_code = $student->student_code;
            dispatch(new AddStudentJob($class_id, $team_id, $student_code));
        }
    }

    public function processQueueCreateEvent()
    {
        $all_class = DB::table('class')->select('class_id', 'team_id')
            ->whereNotNull('team_id')
            ->whereNull('add_event')
            ->groupBy('class_id')
            ->limit(100)                                            
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

    public function deleteAllGroup($team_id, $class_id, $access_token)
    {
        // $access_token = $this->getAccessToken();
        $end_point = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
        $response = Http::withToken($access_token)->delete($end_point);

        DB::beginTransaction();
        DB::table('class')->where('class_id', '=', $class_id)->update([
            'team_id' => null,
            'add_student' => null,
            'add_event' => null,
            'add_instructor' => null,
        ]);
        DB::commit();
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
            dd( $ins->getClassDetail);
           
        }

    }
}
