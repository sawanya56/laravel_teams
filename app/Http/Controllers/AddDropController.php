<?php

namespace App\Http\Controllers;

use App\Jobs\AddStudentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddDropController extends Controller
{

    private $table_add = "adds";
    private $table_drop = "drops";

    public function addStudent()
    {
        $class_all = DB::table($this->table_add)->groupBy('class_id')->get();
        foreach ($class_all as $class) {

            $class_id = $class->class_id;
            $class_detail = DB::table('class')->where('class_id', '=', $class_id)->first();

            if ($class_detail != null) {
                $team_id = $class_detail->team_id;
                if ($team_id != null) {
                    dispatch(new AddStudentJob($class_id, $team_id));
                }
            } else {
                // echo "Team id not found :" . $class_id . "<br>";
                // DB::table('empty_class')->insert(['class_id' => $class_id]);
            }
        }
    }

    public function dropStudent()
    {
        $students = DB::table($this->table_drop)->whereNull('remove_success')->get();
        foreach ($students as $student) {
            $class_id = $student->class_id;
            $class_detail = DB::table('class')->where('class_id', '=', $class_id)->first();
            if ($class_detail != null) {

                $team_id = $class_detail->team_id;
                if ($team_id != null) {
                    $model = new MsController();
                    $model->getStudentMemberId($team_id, $student->student_id, $student->id);
                } else {
                }
            } else {
            }
        }
    }
}
