<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        // dd($request->all());
        $instructor_mail = $request->email;
        $team_id = $request->team_id;
        $class_id = $request->class_id;

        $msTeam = new MsController();
        $access_token = $msTeam->getAccessTokenDatabase();

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

            $request->session()->flash('message', 'Add Success');
            $request->session()->flash('alert', 'alert alert-success');
        } else {
            $message = $response->json();
            $request->session()->flash('message', 'Add Fail :' . $message['error']['message']);
            $request->session()->flash('alert', 'alert alert-danger');
            //Fail
        }

        return redirect('/class/detail/' . $class_id);
    }


    public function addStudent(Request $request)
    {
        // dd($request->all());
        $student_mail = $request->student_mail;
        $team_id = $request->team_id;
        $class_id = $request->class_id;

        $msTeam = new MsController();
        $access_token = $msTeam->getAccessTokenDatabase();

        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
        $student_mail = "https://graph.microsoft.com/v1.0/users/" . $student_mail;
      
        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $student_mail,
        ]);

        if ($response->successful()) {
            // Success
            DB::table('enrollments')->where([
                ['id', '=', $class_id],
                ['email', '=', $request->student_mail],
            ])->update([
                'add_success' => "success",
            ]);

            $class = DB::table('class')->where('class_id', '=', $class_id)->first();
            DB::table('enrollments')->insert([
                'year' => $class->year,
                'term' => $class->term,
                'course_code' => $class->course_code,
                'class_id' => $class_id,
                'section' => $class->section,
                'student_mail' => $request->student_mail,
                'add_by' => "nun",
                'add_success' => "success",
            ]);

            $request->session()->flash('message', 'Add Success');
            $request->session()->flash('alert', 'alert alert-success');
        } else {
            $message = $response->json();
            $request->session()->flash('message', 'Add Fail :' . $message['error']['message']);
            $request->session()->flash('alert', 'alert alert-danger');
            //Fail
        }

        return redirect('/class/detail/' . $class_id);
    }

    public function deleteTeam(Request $request)
    {
        $team_id = $request->team_id;
        $class_id = $request->class_id;
        $model = new MsController();
        $model->deleteTeamAndDatabase($team_id, $class_id);

        return redirect('/main');
    }


    public function getClasscreate()
    {
        return view('class_create');
    }


    public function postClasscreate(Request $request)
    {

         $model = new ClassModel();
         $model->insert($request);
        // dd($request->all());
        return redirect('class/create');
    }
}
