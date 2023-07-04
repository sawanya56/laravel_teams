<?php

namespace App\Http\Controllers;

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
            'schedule' => $schedule,
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

        // $request->session()->flash('message', 'Add Success');
        // $request->session()->flash('message', 'Add Success');
        if ($response->successful()) {
            // Success
            DB::table('instructors')->where('id', '=', $class_id)->update([
                'add_success' => "success",
            ]);
        } else {
            //Fail
        }

        return redirect('/class/detail/' . $class_id);
    }

}
