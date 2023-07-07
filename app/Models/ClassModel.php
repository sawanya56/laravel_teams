<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'class';

    public function insert($request)
    {
        $model = new ClassModel();
        
        $model->year = 2566;
        $model->term = 1;
        $model->team_name = $request->team_name;
        $model->course_code = $request->course_code;
        $model->section = $request->section;
        $model->week_of_day = $request->week_of_day;
        $model->start_time = $request->start_time;
        $model->end_time = $request->end_time;
        $model->duration_time = $request->duration_time;
        $model->class_id = uniqid();

        $model->save();
    }
}
