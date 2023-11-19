<?php

namespace App\Http\Controllers;

use App\Jobs\AddJob;
use App\Jobs\RemoveStudentJob;
use App\Models\AddStudent;
use App\Models\MjuClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AddDropController extends Controller
{

    private $table_add = "adds";
    private $table_drop = "drops";

    public function addStudent()
    {
        $getClass = new MjuClass();
        $class = $getClass->getAllClassGroupBy();
        // dd($class);

        foreach ($class as $row) {
            $team_id = $row->team_id;
          
            if (count($row->getStudentAdd) != 0) {
                // dd($row->getStudentAdd);

                foreach ($row->getStudentAdd as $student) {
                    $class_id = $student->class_id;
                    $student_code = $student->student_mail;
                    // dd($class_id);
                    if ($student_code != null) {
                        AddJob::dispatch($class_id, $team_id, $student_code);
                    }

                }

            }

        }

        
    }

    public function addStudentToTeam($class_id, $team_id, $student_code)
    {
        $model = new AddStudent();

        if ($model->studentAddExist($class_id, $student_code)) {
            return true;
        }

        $access_token = parent::getAccessToken();
        // $student_mail = 'mju' . $student_id . '@mju.ac.th';
        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
        $student_code = "https://graph.microsoft.com/v1.0/users/" . $student_code;

        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $student_code,
        ]);

        $student_status = "";
        $success = true;
        if ($response->successful()) {
            // Success
            $student_status = "student_status";
        } else {
            $error = $response->json();
            $student_status = $error['error']['message'];
            $success = false;
        }

        $model->updateStudentStatusAdd($student_code, $class_id, $student_status);
        return $success;
    }

    public function dropStudent()
    {
        $class_list = DB::table($this->table_drop)->whereNull('remove_success')->groupBy('class_id')->get();
        foreach ($class_list as $class) {
            dispatch(new RemoveStudentJob($class->class_id));
        }
    }
}
