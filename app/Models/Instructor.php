<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $table = 'instructors';
    protected $fillable = [
        'class_id',
        'email',
        'add_success'
    ];

    public function getInstructor($class_id, $email)
    {
        $ins = Instructor::where([
            ['class_id', '=', $class_id],
            ['email', '=', $email]
        ])->first();

        if ($ins == null) {
            $class = MjuClass::where('class_id', '=', $class_id)->first();
            $model_insert = Instructor::insertGetId([
                'year' => $class->year,
                'term' => $class->term,
                'course_code' => $class->course_code,
                'class_id' => $class_id,
                'section' => $class->section,
                'email' => $email,
                'add_by' => "nun",
            ]);
            $ins = Instructor::find($model_insert);
            return $ins;
        } else {
            return $ins;
        }

    }

    public function updateInstructorStatus($id,$status){
        $model = Instructor::find($id);
        $model->add_success = $status;
        $model->save();
    }

    public function getClassDetail(){
        return $this->hasOne(MjuClass::class,"class_id","class_id");
    }

    public function getInstructorClass(){
        return Instructor::whereNull('add_success')->get();
 
     }
}
