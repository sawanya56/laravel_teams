<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MjuClass extends Model
{
    use HasFactory;

    protected $table = 'class';

    public function updateMsTeamId($class_id, $team_id)
    {
        try {
            MjuClass::where('class_id', '=', $class_id)->update([
                'team_id' => $team_id,
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllClass(){
        return MjuClass::whereNotNull('team_id')->get();
    }

    public function getAllClassGroupBy(){
        return MjuClass::whereNotNull('team_id')->groupBy('class_id')->get();
    }

    public function getClassStudentNull()
    {
        $class = MjuClass::whereNotNull('team_id')->whereNull('add_student')->groupBy('class_id')->get();
        return $class;
    }

    public function getClassInstructorNull()
    {
        $class = MjuClass::whereNotNull('team_id')->whereNull('add_instructor')->groupBy('class_id')->get();
        return $class;
    }

    public function getCourse()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_code', 'course_code');
    }

    public function getEnrollment(){
        return $this->hasMany(Enrollment::class,'class_id','class_id');
        
    }
    public function getStudentAdd(){
        return $this->hasMany(AddStudent::class,'class_id','class_id');
        
    }
}
