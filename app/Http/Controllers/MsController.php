<?php

namespace App\Http\Controllers;

use App\Jobs\CreateTeam;
use App\Jobs\DeleteAllTeam;
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

            DB::table('sections')->where('section', '=', $section_id)->update([
                'ms_team_id' => $ms_team_id,
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function AddStudent()
    {

        $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->whereNull('add_student')->get();
        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereIn('section', ['333083', '335260'])->get();
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
        $sections = DB::table('view_sections')->where('section', '=', '336028')->get();
        // $sections = DB::table('view_sections')->select('section', 'ms_team_id')->whereNotNull('ms_team_id')->whereNull('add_event')->get();
        foreach ($sections as $section) {
            $team_id = $section->ms_team_id;
            $section_id = $section->section;
            $group_mail = $this->getGroupmail($team_id, $section_id);
            $channel_id = $this->getChannel($team_id, $section_id);
            // $access_token = $this->getAccessTokenDatabase();

            // dd($group_mail,$channel_id,$access_token,$team_id);
            // $start_date_time = '2023-07-03T12:00:00';
            // $end_date_time = '2023-07-03T13:00:00';
            $start_date = '2023-07-03';
            $end_date = '2023-07-30';

            $class_infomation = DB::table('class')->where('section', '=', $section_id)->get();
            $days_of_week = [];
            foreach ($class_infomation as $row) {

                $start_time = $row->start_time;
                $dulation_time = $row->duration_time;
                $study_time = $this->calculateEndTime($start_time, $dulation_time);
                $start_date_time = $start_date . 'T' . $study_time['start_time'];
                $end_date_time = $start_date . 'T' . $study_time['end_time'];
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
                    "onlineMeetingProvider" => "teamsForBusiness",
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

                //Check Meeting URL In Database
                $meeting_url = DB::table('sections')->where('section', '=', $section_id)->whereNotNull('meeting_url')->first();
                if ($meeting_url != null) {
                    // unset($data['isOnlineMeeting']);
                    // unset($data['onlineMeetingProvider']);
                    $data['onlineMeetingUrl'] = $meeting_url->meeting_url;
                }

                // $token = $this->getAccessToken();
                $token = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6Ik81N1BjZUwtTkQyVE80a1ZIMDYwcTFlNW5KdG5QT0hxVTdIV3plb0h2VzgiLCJhbGciOiJSUzI1NiIsIng1dCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyIsImtpZCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC84ZWM3NGEzOS1kZGY2LTQxZTEtYjBhMi1mZjA0NTllYThlYjgvIiwiaWF0IjoxNjg3Nzg4MjE0LCJuYmYiOjE2ODc3ODgyMTQsImV4cCI6MTY4Nzc5Mjk3NywiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFUUUF5LzhUQUFBQVpkaEpBWk90L3hSRUFIL3V4UXhvWEdjaVhjaDQ2Q3ZWT2JaMUY3N3duVXVsT0Rmc1JCVXJ1Z1B5eGxJZGZzZmwiLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6IkdyYXBoIEV4cGxvcmVyIiwiYXBwaWQiOiJkZThiYzhiNS1kOWY5LTQ4YjEtYThhZC1iNzQ4ZGE3MjUwNjQiLCJhcHBpZGFjciI6IjAiLCJmYW1pbHlfbmFtZSI6IuC4geC4uOC4peC4muC4o-C4tOC4hOC4uOC4myIsImdpdmVuX25hbWUiOiLguJvguKPguLDguYDguKrguKPguLTguJAiLCJpZHR5cCI6InVzZXIiLCJpcGFkZHIiOiIxLjIwLjE0MS4xMzQiLCJuYW1lIjoi4Lib4Lij4Liw4LmA4Liq4Lij4Li04LiQICDguIHguLjguKXguJrguKPguLTguITguLjguJsiLCJvaWQiOiJiNmY0NDZmZS04NDI2LTQzODMtOTlmOS04MWEzYjRiMmYwMTkiLCJvbnByZW1fc2lkIjoiUy0xLTUtMjEtNzkwNTI1NDc4LTEwNzgwODE1MzMtODM5NTIyMTE1LTY4OTUyNiIsInBsYXRmIjoiMyIsInB1aWQiOiIxMDAzMjAwMDM5MjQyMDQwIiwicmgiOiIwLkFWTUFPVXJIanZiZDRVR3dvdjhFV2VxT3VBTUFBQUFBQUFBQXdBQUFBQUFBQUFCVEFPby4iLCJzY3AiOiJDYWxlbmRhcnMuUmVhZFdyaXRlIENoYW5uZWwuUmVhZEJhc2ljLkFsbCBDaGFubmVsTWVzc2FnZS5TZW5kIENoYXQuUmVhZCBDaGF0LlJlYWRCYXNpYyBDaGF0LlJlYWRXcml0ZSBDaGF0TWVzc2FnZS5TZW5kIENvbnRhY3RzLlJlYWRXcml0ZSBEZXZpY2VNYW5hZ2VtZW50UkJBQy5SZWFkLkFsbCBEZXZpY2VNYW5hZ2VtZW50U2VydmljZUNvbmZpZy5SZWFkLkFsbCBGaWxlcy5SZWFkV3JpdGUuQWxsIEdyb3VwLlJlYWRXcml0ZS5BbGwgSWRlbnRpdHlSaXNrRXZlbnQuUmVhZC5BbGwgTWFpbC5SZWFkIE1haWwuUmVhZFdyaXRlIE1haWxib3hTZXR0aW5ncy5SZWFkV3JpdGUgTm90ZXMuUmVhZFdyaXRlLkFsbCBvcGVuaWQgUGVvcGxlLlJlYWQgUGxhY2UuUmVhZCBQcmVzZW5jZS5SZWFkIFByZXNlbmNlLlJlYWQuQWxsIFByaW50ZXJTaGFyZS5SZWFkQmFzaWMuQWxsIFByaW50Sm9iLkNyZWF0ZSBQcmludEpvYi5SZWFkQmFzaWMgcHJvZmlsZSBSZXBvcnRzLlJlYWQuQWxsIFNpdGVzLlJlYWRXcml0ZS5BbGwgVGFza3MuUmVhZFdyaXRlIFRlYW0uQ3JlYXRlIFRlYW0uUmVhZEJhc2ljLkFsbCBVc2VyLlJlYWQgVXNlci5SZWFkQmFzaWMuQWxsIFVzZXIuUmVhZFdyaXRlIFVzZXIuUmVhZFdyaXRlLkFsbCBlbWFpbCIsInN1YiI6IkY5RUxZQnppSFVLYXFtWDJhOTlFT2dZU2V5RzFHUm44Vl9NVk9vdHVQSDQiLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiQVMiLCJ0aWQiOiI4ZWM3NGEzOS1kZGY2LTQxZTEtYjBhMi1mZjA0NTllYThlYjgiLCJ1bmlxdWVfbmFtZSI6InByYXNlcnRfa2JAbWp1LmFjLnRoIiwidXBuIjoicHJhc2VydF9rYkBtanUuYWMudGgiLCJ1dGkiOiIybVdnRGk2M0UwR0xEX0l5dGJFeEFBIiwidmVyIjoiMS4wIiwid2lkcyI6WyJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX2NjIjpbIkNQMSJdLCJ4bXNfc3NtIjoiMSIsInhtc19zdCI6eyJzdWIiOiJ3YnpKSlFpNmlRU2UtYzhmaGE3NkhtOGdyczJ6MzZwZ3o1aktrZG9ab1pJIn0sInhtc190Y2R0IjoxMzkzMjE0NjkzfQ.DMbFkg4h5sSu3v76--z5icj7dqsC7OGE7gTUZgk-LzeS4F4hwSTUnD3sEyP4mOKrtOtSGIuvYfqfOTWAUCWIVWmPSYwIKU50ZQA0uB6y5JXb2eiuVeis5ZH0UjlO-fN1g6AsvXPRUSDjAc5AwFYoodXYsPbq4a-pvmqWD2_Mi7_1BpWPhMcgarxAODoLYCoq0rYwkGnRxtE_JwMn-XC31fkVApLZJzapUuJvjhvWykGTLRP2xZ1pUWwgpuCZpNRDNQyecpK72byyTA1fVDR0xJDuk8J_ScTH9clrXzDeOKRXn5e9cAVVxXTf0wtrgjacNLDzrJxcx6i2rjZU1MWWnA';
                $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id . "/calendar/events";

                $retuen = [
                    'data' => $data,
                    'end_point' => $endpoint,
                ];
                // return response()->json($retuen, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);

                $response = Http::withToken($token)->post($endpoint, $data);
                $response_data = $response->json();

                // dd($response,$response->json());
                if (isset($response_data['error'])) {
                    dd($response);
                } else {
                    //Create Success
                    // dd($response_data);
                    $event_id = $response['id'];
                    if ($meeting_url == null) {
                        $meeting_url = $response['onlineMeeting']['joinUrl'];
                        DB::table('sections')->where('section', '=', $section_id)->update([
                            'meeting_url' => $meeting_url,
                        ]);
                    }
                    $body_content = $response['body'];
                    // dd($section_id,$meeting_url,$event_id);
                    // DB::beginTransaction();

                    DB::table('class')->where('id', '=', $row->id)->update([
                        'event_id' => $event_id,
                    ]);
                    // DB::commit();
                    echo "Success";
                    // dd($event_id,$meeting_url ,$body_content);
                    // $this->postMeetingToTeam($team_id, $channel_id, $body_content);
                }
            }
        }
    }

    public function deleteAllGroup($team_id, $section_id, $access_token)
    {
        // $access_token = $this->getAccessToken();
        $end_point = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
        $response = Http::withToken($access_token)->delete($end_point);

        DB::beginTransaction();
        DB::table('sections')->where('section', '=', $section_id)->update([
            'ms_team_id' => null,
        ]);
        DB::commit();
    }

    public function processQueueDeleteAllTeam()
    {
        $sections = DB::table('view_sections')->whereNotNull('ms_team_id')->get();
        $access_token = $this->getAccessToken();
        foreach ($sections as $section) {
            dispatch(new DeleteAllTeam($section->section, $section->ms_team_id, $access_token));
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
        $sections = DB::table('view_sections')->whereNull('ms_team_id')->get();
        // $sections = DB::table('view_sections')->where('section', '=', '336028')->get();

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
