<?php

namespace App\Http\Controllers;

use App\Jobs\CreateTeam;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MsController extends Controller
{

    public function getAccessToken()
    {
        $tenantId = env('TENANT_ID');
        $clientId = env('CLIENT_ID');
        $clientSecret = env('CLIENT_SECRET');

        // Create a Microsoft 365 group
        $tokenEndpoint = "https://login.microsoftonline.com/$tenantId/oauth2/token";

        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
            'resource' => 'https://graph.microsoft.com'
        ];

        $ch = curl_init($tokenEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($response, true);

        $now = new DateTime();
        DB::table('settings')->insert([
            'access_token' => $tokenData['access_token'],
            'created_at' => $now->format('Y-m-d H:i:s')
        ]);

        return $tokenData['access_token'];
    }

    public function CreateTeams($team_name, $section_id, $description)
    {
        $model = DB::table('settings')->orderBy('id', 'desc')->first();
        $access_token = null;
        if ($model == null) {
            $access_token = $this->getAccessToken();
        } else {
            $access_token = $model->access_token;
        }

        try {
            $response = Http::withToken($access_token)->post('https://graph.microsoft.com/v1.0/teams', [
                "template@odata.bind" => "https://graph.microsoft.com/v1.0/teamsTemplates('educationClass')",
                "displayName" => $team_name,
                "description" => $description,
            ]);

            $result = $response->headers();
            $ms_team_id = $result['Content-Location'][0];
            $ms_team_id = str_replace("/teams('", "", $ms_team_id);
            $ms_team_id = str_replace("')", "", $ms_team_id);

            DB::table('sections')->where('section', '=', $section_id)->update([
                'ms_team_id' => $ms_team_id,
            ]);
        } catch (Exception $e) {
            //ทำอีกครั้งถ้า error แต่ get Access Token อีกครั้ง
            $access_token = $this->getAccessToken();
            $response = Http::withToken($access_token)->post('https://graph.microsoft.com/v1.0/teams', [
                "template@odata.bind" => "https://graph.microsoft.com/v1.0/teamsTemplates('educationClass')",
                "displayName" => $team_name,
                "description" => $description,
            ]);

            $result = $response->headers();
            $ms_team_id = $result['Content-Location'][0];
            $ms_team_id = str_replace("/teams('", "", $ms_team_id);
            $ms_team_id = str_replace("')", "", $ms_team_id);

            DB::table('sections')->where('section', '=', $section_id)->update([
                'ms_team_id' => $ms_team_id,
            ]);
        }
    }

    public function AddStudent()
    {

        $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->get();
        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->limit(10)->get();

        foreach ($sections as $section) {
            $section_id = $section->section;
            $team_id = $section->ms_team_id;

            $students = DB::table('enrollments')->where('section', '=', $section_id)->get();

            foreach ($students as $student) {
                $student_mail = $student->student_mail;
                //CALL API TEAM
                $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
                $student_mail = "https://graph.microsoft.com/v1.0/users/" . $student_mail;
                $response = Http::withToken($this->token)->post($url, [
                    "@odata.id" => $student_mail,
                ]);
                //END CALL API
            }
        }
    }

    public function AddInstructor()
    {

        $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->get();
        foreach ($sections as $section) {
            $section_id = $section->section;
            $team_id = $section->ms_team_id;

            $instructors = DB::table('view_instructors')->where('section', '=', $section_id)->get();
            foreach ($instructors as $item) {
                $instructor_mail = $item->instructor_mail;
                $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';
                $instructor_mail = "https://graph.microsoft.com/v1.0/users/" . $instructor_mail;
                // echo $url."|".$instructor_mail."<br>";
                $response = Http::withToken($this->token)->post($url, [
                    "@odata.id" => $instructor_mail,

                ]);
            }
        }
    }

    public function getGroupmail($team_id = "bd5f3682-eb31-4b17-8bdd-4897183af1b0", $section_id = 336028)
    {
        $endpoint = "https://graph.microsoft.com/v1.0/groups/ " . $team_id;
        $endpoint = str_replace(" ", "", $endpoint);
        $response = Http::withToken($this->token)->get($endpoint);
        $mail = $response->json();

        DB::table('class')->where('section', '=', $section_id)->update([
            'group_mail' => $mail['mail'],
        ]);
        return $mail['mail'];
    }

    public function CreateEvent($data)
    {
        $data['team_id'];
    }

    public function deleteAllGroup()
    {
        $sections = DB::table('view_sections')->whereNotNull('ms_team_id')->get();
        foreach ($sections as $section) {
            $team_id = $section->ms_team_id;
            $section_id = $section->section;
            $end_point = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
            $response = Http::withToken($this->token)->delete($end_point);

            DB::table('sections')->where('section', '=', $section_id)->update([
                'ms_team_id' => null,
            ]);
        }
    }

    //----------------------------------------- QUEUE -----------------------------------------------//
    public function processQueueCreateTeam()
    {
        $sections = DB::table('view_sections')->whereNull('ms_team_id')->get();

        foreach ($sections as $section) {

            $team_name = $section->team_name;
            $section_id = $section->section;
            $description = $section->description;

            dispatch(new CreateTeam($team_name, $section_id, $description));
        }
    }
}
