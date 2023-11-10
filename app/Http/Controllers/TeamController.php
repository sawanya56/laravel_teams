<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Instructor;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\MjuClass;

class TeamController extends Controller
{
    //


    public function createTeams($team_name, $class_id, $description)
    {
        $access_token = parent::getAccessToken();
        $owner_email = 'sawanya_kck@mju.ac.th';
        try {
            $response = Http::withToken($access_token)->post('https://graph.microsoft.com/v1.0/teams', [
                "template@odata.bind" => "https://graph.microsoft.com/v1.0/teamsTemplates('educationClass')",
                "displayName" => $team_name,
                "description" => $description,
                "members" => [
                    [
                        "@odata.type" => "#microsoft.graph.aadUserConversationMember",
                        "roles" => ["owner"],
                        "user@odata.bind" => "https://graph.microsoft.com/v1.0/users/" . $owner_email,
                    ],
                ],
            ]);

            $result = $response->headers();
            $ms_team_id = $result['Content-Location'][0];
            $ms_team_id = str_replace("/teams('", "", $ms_team_id);
            $ms_team_id = str_replace("')", "", $ms_team_id);

            $model = new MjuClass();
            $model->updateMsTeamId($class_id, $ms_team_id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function AddInstructor($class_id, $team_id, $instructor_mail)
    {
        $access_token = parent::getAccessToken();
        $instructor_mail = strtolower($instructor_mail);
        $instructor_mail = trim($instructor_mail);
        $model = new Instructor();
        $ins = $model->getInstructor($class_id, $instructor_mail);

        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';
        $instructor_mail = "https://graph.microsoft.com/v1.0/users/" . $instructor_mail;
        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $instructor_mail,
        ]);

        $status = "";
        $success = true;
        if ($response->successful()) {
            $status = "success";
        } else {
            $message = $response->json();
            $status = $message['error']['message'];
            $success = false;
        }

        $model->updateInstructorStatus($ins->id, $status);
        return $success;
    }

    public function AddStudent($class_id, $team_id, $student_id)
    {
        $model = new Enrollment();

        if($model->studentEnrollExist($class_id,$student_id)){
            return true;
        }

        $access_token = parent::getAccessToken();
        $student_mail = 'mju'.$student_id.'@mju.ac.th';
        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
        $student_mail = "https://graph.microsoft.com/v1.0/users/" . $student_mail;

        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $student_mail,
        ]);

        $student_status = "";
        $success = true;
        if ($response->successful()) {
            // Success
            $student_status = "student_status";
        } else {
            $error = $response->json();
            $student_status = $error['error']['message'];
            $success = false;
        }

        $model->updateStudentStatus($student_id,$class_id,$student_status);
        return $success;
    }
}
