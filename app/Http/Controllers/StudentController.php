<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public $var1 = "1";
    protected $var2 = "2";
    private $var3 = "3";

    public function getStudent(){
        echo "Student 1";
    }

    public function getStudent2(){
        echo "Student 2";
    }

    public function summation($num1,$num2){
        return $num1 * $num2;
    }
}
   

