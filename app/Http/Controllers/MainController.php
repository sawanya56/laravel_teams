<?php

namespace App\Http\Controllers;

use App\Jobs\AddStudentKasetJob;
use App\Jobs\CreateTeam;
use App\Models\ClassModel;
use App\Models\StudentKaset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{

    private $token = 'token';

    public function main()
    {
        $teams = DB::table('class')->groupBy('class_id')->get();
        return view('main', [
            'teams' => $teams,
        ]);
    }

    public function getClassDetail($class_id)
    {
        $ins = DB::table('instructors')->where('class_id', '=', $class_id)->get();
        $students = DB::table('enrollments')->where('class_id', '=', $class_id)->get();
        $schedule = DB::table('class')->where('class_id', '=', $class_id)->get();
        $class_detail = DB::table('class')->where('class_id', '=', $class_id)->first();
        return view('class_detail', [
            'instructors' => $ins,
            'students' => $students,
            'class_detail' => $class_detail,
            'schedules' => $schedule,
        ]);
    }

    public function addOwner(Request $request)
    {
        $instructor_mail = $request->email;
        $team_id = $request->team_id;
        $class_id = $request->class_id;

        $access_token = parent::getAccessToken();

        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';
        $instructor_mail = "https://graph.microsoft.com/v1.0/users/" . $instructor_mail;
        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $instructor_mail,

        ]);

        if ($response->successful()) {
            // Success
            DB::table('instructors')->where([
                ['id', '=', $class_id],
                ['email', '=', $request->email],
            ])->update([
                'add_success' => "success",
            ]);

            $class = DB::table('class')->where('class_id', '=', $class_id)->first();
            DB::table('instructors')->insert([
                'year' => $class->year,
                'term' => $class->term,
                'course_code' => $class->course_code,
                'class_id' => $class_id,
                'section' => $class->section,
                'email' => $request->email,
                'add_by' => "nun",
                'add_success' => "success",
            ]);

            $request->session()->flash('message', 'Add Owner Success');
            $request->session()->flash('alert', 'alert alert-success');
        } else {
            $message = $response->json();
            $request->session()->flash('message', 'Add Owner Fail :' . $message['error']['message']);
            $request->session()->flash('alert', 'alert alert-danger');
            //Fail
        }

        return redirect('/class/detail/' . $class_id);
    }

    public function postAddStudent(Request $request)
    {
        // dd($request->all());
        // $student_mail = $request->email;
        $student_mail = $request->student_code;
        $team_id = $request->team_id;
        $class_id = $request->class_id;
        // dd($class_id);

        $student_mail = "mju" . $student_mail . "@mju.ac.th";

        $access_token = parent::getAccessToken();

        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
        $student = "https://graph.microsoft.com/v1.0/users/" . $student_mail;

        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $student,
        ]);
        $valid = $response->successful();

        if ($valid) {
            $enroll = DB::table('enrollments')->where('class_id', '=', $class_id)->first();

            if ($enroll == null) {
                $enroll = DB::table('class')->where('class_id', '=', $class_id)->first();
            }

            DB::table('enrollments')->insert([
                'year' => $enroll->year,
                'term' => $enroll->term,
                'course_code' => $enroll->course_code,
                'class_id' => $class_id,
                'section' => $enroll->section,
                'student_mail' => $student_mail,
                'add_success' => 'success',
            ]);

            $request->session()->flash('message', 'Add Student Success');
            $request->session()->flash('alert', 'alert alert-success');
        } else {
            $message = $response->json();
            $request->session()->flash('message', 'Add Fail :' . $message['error']['message']);
            $request->session()->flash('alert', 'alert alert-danger');
            //Fail
        }

        return redirect('/class/detail/' . $class_id);
        // dd($response);

    }

    public function deleteTeam(Request $request)
    {
        $team_id = $request->team_id;
        $class_id = $request->class_id;
        $model = new TeamController();
        $model->deleteTeamAndDatabase($team_id, $class_id);

        return redirect('/home');
    }

    public function getClasscreate()
    {
        return view('class_create');
    }

    public function postClasscreate(Request $request)
    {
        $team_name = $request->team_name;
        $course_code = $request->course_code;
        $section = $request->section;
        $week_of_day = $request->week_of_day;
        $start_time = $request->start_time;
        $end_time = $request->end_time;
        $duration_time = $request->duration_time;

        $class_id = uniqid();
        $model = new ClassModel();
        $result = $model->insertClass($class_id, $team_name, $course_code, $section, $week_of_day, $start_time, $end_time, $duration_time);

        CreateTeam::dispatch($team_name, $class_id, "-");

        if ($result == true) {
            return response()->json([
                'status' => 'success',

            ]);
        } else {
            return response()->json([
                'status' => 'fail',
            ]);
        }
        // return redirect('class/create');
    }

    public function postRemoveStudent(Request $request)
    {
        // dd('NOT WORK');
        $team_id = $request->team_id;
        $student_mail = $request->email;
        $class_id = $request->class_id;
        $model = new TeamController();
        $token = $model->getAccessToken();
        $getMembersUrl = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/members";
        $response = Http::withToken($token)->get($getMembersUrl);
        $members = $response->json()['value'] ?? [];
        $memberId = null;

        foreach ($members as $member) {
            if (strtolower($member['email']) === strtolower($student_mail)) {

                $memberId = $member['id'];
                $token = parent::getAccessToken();
                $removeMemberUrl = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/members/" . $memberId;
                $response = Http::withToken($token)->delete($removeMemberUrl);
                if ($response->successful()) {
                    $enroll = DB::table('enrollments')->where([
                        ['class_id', '=', $class_id],
                        ['student_mail', '=', $student_mail],
                    ])->delete();

                    $request->session()->flash('message', 'Remove Student Success');
                    $request->session()->flash('alert', 'alert alert-success');
                } else {
                    $message = $response->json();
                    dd($message, $removeMemberUrl, $memberId);
                    $request->session()->flash('message', 'Add Student Fail :' . $message['error']['message']);
                    $request->session()->flash('alert', 'alert alert-danger');
                    //Fail

                }
                break;
            }
        }

        return redirect('/class/detail/' . $class_id);
    }

    public function createTeam(Request $request)
    {
        $team_name = $request->team_name;
        $class_id = $request->class_id;
        $description = $request->description;

        $model = new TeamController();
        $model->createTeams($team_name, $class_id, $description);

        return redirect()->route('nun');
        // return redirect()->back();
        // return redirect('test');
        //กลับหน้า from
    }

    public function getCreateTeam()
    {
        return view('');
        //หน้าfrom
    }

    public function AddStudentForKaset()
    {
        $students = DB::table('students')->whereNull('add_success')->groupBy('class_id')->get();


        foreach ($students as $student) {
            $student_mail = $student->student_mail;
            $class_id = $student->class_id;

            $class_detail = DB::table('class')->where('class_id', '=', $class_id)->first();

            if ($class_detail != null) {
                $team_id = $class_detail->team_id;

                    // dd($student_mail);
                    if ($student_mail != null) {
                        AddStudentKasetJob::dispatch($class_id, $team_id, $student_mail);
                    }
                }
            }
        }
    }

    public function addStudentKasetToTeam($class_id, $team_id, $student_mail)
    {
        $model = new StudentKaset();

        if ($model->studentAddExist($class_id, $student_mail)) {
            return true;
        }

        $access_token = parent::getAccessToken();
        $url = "https://graph.microsoft.com/v1.0/groups/{$team_id}/members/\$ref";
        $student_mail_add = "https://graph.microsoft.com/v1.0/users/{$student_mail}";

        $payload = [
            "@odata.id" => $student_mail_add,
        ];

        $response = Http::withToken($access_token)->post($url, $payload);

        $student_status = "";
        $success = true;

        if ($response->successful()) {
            $student_status = "success";
            Log::info("ADD Student success", [
                'student_mail' => $student_mail,
            ]);
        } else {
            $error = $response->json();
            $student_status = $error['error']['message'];
            $success = false;
            Log::error("ADD Student error", [
                'student_mail' => $student_mail,
                'error' => $student_status,
            ]);
        }

        $model->updateStudentStatusAdd($student_mail, $class_id, $student_status);

        return $success;
    }

}
