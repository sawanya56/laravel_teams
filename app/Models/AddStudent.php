<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddStudent extends Model
{
    use HasFactory;

    protected $table = "adds";
    protected $fillable = [
        'class_id',
        'student_mail',
        'add_success'
    ];

    public function updateStudentStatusAdd($student_mail, $class_id, $student_status)
    {
        AddStudent::where([
            ['class_id', '=', $class_id],
            ['student_mail', '=', $student_mail]
        ])->update([
            'add_success' => $student_status
        ]);
    }

    public function studentAddExist($class_id, $student_mail)
    {
        $student = AddStudent::where([
            ['class_id', '=', $class_id],
            ['student_mail', '=', $student_mail],
            ['add_success', '=', "success"]
        ])->first();

        if ($student != null) {
            return true;
        }
        return false;
    }

   
}
