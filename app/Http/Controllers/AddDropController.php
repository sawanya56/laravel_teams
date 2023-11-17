<?php

namespace App\Http\Controllers;

use App\Jobs\AddJob;
use App\Jobs\RemoveStudentJob;
use App\Models\AddStudent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AddDropController extends Controller
{

    private $table_add = "adds";
    private $table_drop = "drops";

    public function addStudent()
    {
        $class_all = DB::table($this->table_add)->groupBy('class_id')->get();
        // $student_mail =  $class_all->student_mail;
       
        dd($class_all[0]->student_mail);
        
        foreach ($class_all as $class) {

            $class_id = $class->class_id;
            $class_detail = DB::table('class')->where('class_id', '=', $class_id)->first();
            // DB::table('add')->update([ 'add_success' => 'success']);
            if ($class_detail != null) {
                $team_id = $class_detail->team_id;

                dd($team_id);
                if ($team_id != null) {
                    AddJob::dispatch($class_id, $team_id, $student_mail);
                }
            } else {
                // echo "Team id not found :" . $class_id . "<br>";
                // DB::table('empty_class')->insert(['class_id' => $class_id]);
            }
        }
    }

    public function addStudentToTeam($class_id, $team_id, $student_mail)
    {
        $model = new AddStudent();

        if ($model->studentAddExist($class_id, $student_mail)) {
            return true;
        }

        $access_token = parent::getAccessToken();
        // $student_mail = 'mju' . $student_id . '@mju.ac.th';
        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
        $student_mail = "https://graph.microsoft.com/v1.0/users/" . $student_mail;

        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $student_mail,
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

        $model->updateStudentStatusAdd($student_mail, $class_id, $student_status);
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
