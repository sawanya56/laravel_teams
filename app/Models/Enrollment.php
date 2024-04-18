<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
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

    public function updateStudentStatus($student_code, $class_id, $student_status)
    {
        try{
            Enrollment::where([
                ['class_id', '=', $class_id],
                ['student_code', '=', $student_code]
            ])->update([
                'add_success' => $student_status
            ]);
        }catch(Exception $e){
            Log::error("udpate student status error ",[
                'student_code' => $student_code,
                'class_id' => $class_id
            ]);
        }
       
    }

    public function studentEnrollExist($class_id, $student_code)
    {
        $student = Enrollment::where([
            ['class_id', '=', $class_id],
            ['student_code', '=', $student_code],
            ['add_success', '=', "success"]
        ])->first();

        if ($student != null) {
            return true;
        }
        return false;
    }

    public function getClass(){
        return $this->hasOne(MjuClass::class,"class_id","class_id");
    }

    public function getStudentClass(){
       return Enrollment::whereNull('add_success')->get();

    }

    
}
