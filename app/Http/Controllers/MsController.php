<?php

namespace App\Http\Controllers;

use App\Jobs\CreateTeam;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MsController extends Controller
{

    public $week_of_day = [
        'MO' => "monday",
        'TU' => "tuesday",
        'WE' => "wednesday",
        'TH' => "thursday",
        'FR' => "friday",
        'SA' => "saturday",
        'SU' => "sunday",

    ];

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
            'resource' => 'https://graph.microsoft.com',
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
            'created_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return $tokenData['access_token'];
    }

    public function getAccessTokenDatabase()
    {
        return $this->getAccessToken();
        $model = DB::table('settings')->orderBy('id', 'desc')->first();
        $access_token = null;
        if ($model == null) {
            $access_token = $this->getAccessToken();
        } else {
            $access_token = $model->access_token;
        }

        return $access_token;
    }

    public function CreateTeams($team_name, $section_id, $description)
    {

        $access_token = $this->getAccessTokenDatabase();

        $owner_email = 'mju6204101356@mju.ac.th';
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

            DB::table('sections')->where('section', '=', $section_id)->update([
                'ms_team_id' => $ms_team_id,
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function AddStudent()
    {

        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->whereNull('add_student')->get();
        $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereIn('section', ['333083', '335260'])->get();
        foreach ($sections as $section) {
            $section_id = $section->section;
            $team_id = $section->ms_team_id;

            $students = DB::table('enrollments')->where('section', '=', $section_id)->get();
            $access_token = $this->getAccessTokenDatabase();

            foreach ($students as $student) {
                $student_mail = $student->student_mail;
                //CALL API TEAM
                $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
                $student_mail = "https://graph.microsoft.com/v1.0/users/" . $student_mail;

                $response = Http::withToken($access_token)->post($url, [
                    "@odata.id" => $student_mail,
                ]);

                echo $url . '<br>';
                echo $student_mail . "<br>";
                //END CALL API
            }
            DB::table('sections')->where('section', '=', $section->section)->update([
                'add_student' => 'success',
            ]);
        }
    }

    public function AddInstructor()
    {
        $access_token = $this->getAccessTokenDatabase();
        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->get();
        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->whereNull('add_instructor')->get();
        $sections = DB::table('view_sections')->select('section', 'ms_team_id')->where('section', '=', '335269')->get();
        foreach ($sections as $section) {
            $section_id = $section->section;
            $team_id = $section->ms_team_id;

            $instructors = DB::table('view_instructors')->where('section', '=', $section_id)->get();
            foreach ($instructors as $item) {
                $instructor_mail = $item->instructor_mail;
                $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';
                $instructor_mail = "https://graph.microsoft.com/v1.0/users/" . $instructor_mail;
                // echo $url."|".$instructor_mail."<br>";
                $response = Http::withToken($access_token)->post($url, [
                    "@odata.id" => $instructor_mail,

                ]);

                echo $instructor_mail . "<br>";
            }
            DB::table('sections')->where('section', '=', $section->section)->update([
                'add_instructor' => 'success',
            ]);
        }
    }

    public function getGroupmail($team_id, $section_id)
    {
        $access_token = $this->getAccessTokenDatabase();
        $endpoint = "https://graph.microsoft.com/v1.0/groups/ " . $team_id;
        $endpoint = str_replace(" ", "", $endpoint);
        $response = Http::withToken($access_token)->get($endpoint);
        $mail = $response->json();

        DB::table('class')->where('section', '=', $section_id)->update([
            'group_mail' => $mail['mail'],
        ]);
        return $mail['mail'];
    }

    public function getChannel($team_id, $section_id)
    {
        $access_token = $this->getAccessTokenDatabase();
        $endpoint = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/channels";
        $endpoint = str_replace(" ", "", $endpoint);
        $response = Http::withToken($access_token)->get($endpoint);
        $channel_id = $response->json();
        $channel_id = $channel_id['value'][0]['id'];
        DB::table('sections')->where('section', '=', $section_id)->update([
            'channel_id' => $channel_id,
        ]);
        return $channel_id;
    }

    public function CreateEvent()
    {
        $sections = DB::table('view_sections')->where('section','=', '336028')->get();
        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->whereNull('add_event')->get();
        foreach ($sections as $section) {
            $team_id = $section->ms_team_id;
            $section_id = $section->section;
            $group_mail = $this->getGroupmail($team_id, $section_id);
            $channel_id = $this->getChannel($team_id, $section_id);
            $access_token = $this->getAccessTokenDatabase();

            // dd($group_mail,$channel_id,$access_token,$team_id);
            $start_date_time = '2023-07-03T12:00:00';
            $end_date_time = '2023-07-03T13:00:00';
            $start_date = '2023-07-03';
            $end_date = '2023-07-30';

            $class_infomation = DB::table('class')->where('section', '=', $section_id)->get();
            $days_of_week = [];
            foreach ($class_infomation as $row) {
                $start_time = $row->start_time;
                $dulation_time = $row->duration_time;
                $study_time = $this->calculateEndTime($start_time, $dulation_time);
                $start_date_time = $start_date . 'T' . $study_time['start_time'];
                $end_date_time = $end_date . 'T' . $study_time['end_time'];
                // dd($row);
                $day = strtoupper($row->week_of_day);
                $days_of_week = $this->week_of_day[$day];
                $data = [

                    "subject" => $section->calendar_subject,
                    "body" => [
                        "contentType" => "Text",
                        "content" => $row->study_type,
                    ],
                    "start" => [
                        "dateTime" => $start_date_time,
                        "timeZone" => "Asia/Bangkok",
                    ],
                    "end" => [
                        "dateTime" => $end_date_time,
                        "timeZone" => "Asia/Bangkok",
                    ],
                    "location" => [
                        "displayName" => $row->room_name,
                    ],
                    "attendees" => [
                        [
                            "emailAddress" => [
                                "address" => $group_mail,
                                "name" => "GROUP MAIL",
                            ],
                            "type" => "required",
                        ],
                    ],
                    "isOnlineMeeting" => true,
                    "onlineMeetingProvider" > "teamsForBusiness",
                    "recurrence" => [
                        "pattern" => [
                            "type" => "weekly",
                            "interval" => 1,
                            "daysOfWeek" => [
                                $days_of_week,
                            ],
                        ],
                        "range" => [
                            "type" => "endDate",
                            "startDate" => $start_date,
                            "endDate" => $end_date,
                        ],
                    ],

                ];
                $token = $this->getAccessToken();
                $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id . "/calendar/events";
                $response = Http::withToken($token)->get($endpoint, $data);
                // return response()->json($endpoint, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
                // JSON_UNESCAPED_UNICODE);
                // dd($response->json(),$data);
                $response_data = $response->json();
                dd($response_data);
                $body_content = $response_data['body'];
                $this->postMeetingToTeam($team_id, $channel_id, $body_content);
            }
        }
    }

    public function deleteAllGroup()
    {
        $sections = DB::table('view_sections')->whereNotNull('ms_team_id')->get();
        $access_token = $this->getAccessTokenDatabase();
        foreach ($sections as $section) {
            $team_id = $section->ms_team_id;
            $section_id = $section->section;
            $end_point = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
            $response = Http::withToken($access_token)->delete($end_point);

            DB::table('sections')->where('section', '=', $section_id)->update([
                'ms_team_id' => null,
            ]);
        }
    }

    public function postMeetingToTeam($team_id, $channel_id, $body_content)
    {
        $access_token = $this->getAccessTokenDatabase();
        $end_point = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/channels/" . $channel_id . "/messages";
        $data = [
            "body" => $body_content,
        ];
        $response = Http::withToken($access_token)->post($end_point, $data);
    }

    //----------------------------------------- QUEUE -----------------------------------------------//
    public function processQueueCreateTeam()
    {
        // $sections = DB::table('view_sections')->whereNull('ms_team_id')->limit(1)->get();
        $sections = DB::table('view_sections')->whereNull('ms_team_id')->get();
// dd($sections);
        foreach ($sections as $section) {

            $team_name = $section->team_name;
            $section_id = $section->section;
            $description = $section->description;

            // $this->createTeams($team_name, $section_id, $description);
            // echo "Create Team";
            // $this->AddStudent();
            // echo "Add Student";
            // $this->AddInstructor();
            // echo "AddInstructor";
            // $this->CreateEvent();
            // echo "Create Event";
            dispatch(new CreateTeam($team_name, $section_id, $description));
        }
    }

    public function calculateEndTime($start_time, $dulation_time)
    {
        $start_time = new Datetime($start_time);
        $incress_time = "+ " . $dulation_time . " minutes";

        $end_time = date('Y-m-d H:i', strtotime($incress_time, strtotime($start_time->format('H:i'))));
        $end_time = new DateTime($end_time);

        $new_start_time = $start_time->format('H:i');
        $new_end_time = $end_time->format('H:i');

        return [
            'start_time' => $new_start_time,
            'end_time' => $new_end_time,
        ];
    }
}

// b50af24c-edf6-432a-8762-90e953b824d7
