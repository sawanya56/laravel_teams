<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function getStudentByClassId(Request $request){
        
        $class_id = $request->class_id;
        $student= DB::table('enrollments')->where('class_id' , '=', $class_id)->get();
        return response()->json($student);
    }
}
