<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PDO;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = "enrollments";
    protected $fillable = [
        'class_id',
        'student_code',
        'add_success'
    ];

    public function updateStudentStatus($student_id, $class_id, $status)
    {
        Enrollment::where([
            ['class_id', '=', $class_id],
            ['student_code', '=', $student_id]
        ])->update([
            'add_success' => $status
        ]);
    }

    public function studentEnrollExist($class_id, $student_id)
    {
        $student = Enrollment::where([
            ['class_id', '=', $class_id],
            ['student_code', '=', $student_id],
            ['add_success', '=', "success"]
        ])->first();

        if ($student != null) {
            return true;
        }
        return false;
    }
}
