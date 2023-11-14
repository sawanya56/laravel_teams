<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'class';
    protected $fillable = [
        'year','term','team_name','course_code', 'section','week_of_day','start_time','end_time','duration_time','class_id'
    ];
    public function insertClass($class_id,$team_name, $course_code, $section, $week_of_day, $start_time, $end_time, $duration_time)
    {
        $model = new ClassModel();

        $model->year = 2566;
        $model->term = 1;
        $model->team_name = $team_name;
        $model->course_code = $course_code;
        $model->section = $section;
        $model->week_of_day = $week_of_day;
        $model->start_time = $start_time;
        $model->end_time = $end_time;
        $model->duration_time = $duration_time;
        $model->class_id = $class_id;

        $model->save();

        if($model == null){
            return false;
        }else{
            return true;
        }
       
    }
}
