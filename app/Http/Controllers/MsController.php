<?php

namespace App\Http\Controllers;

use App\Jobs\AddInstructorJob;
use App\Jobs\AddStudentJob;
use App\Jobs\CreateEventJob;
use App\Jobs\CreateTeam;
use App\Jobs\DeleteAllTeam;
use App\Jobs\GetGroupMailAndChannelIdJob;
use App\Jobs\PostMessageToTeam;
use App\Models\MjuClass;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // $model = DB::table('settings')->orderBy('id', 'desc')->first();
        // $access_token = null;
        // if ($model == null) {
        //     $access_token = $this->getAccessToken();
        // } else {
        //     $access_token = $model->access_token;
        // }

        // return $access_token;
    }

    public function CreateTeams($team_name, $class_id, $description)
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

            $model = new MjuClass();
            $model->updateMsTeamId($class_id, $ms_team_id);
        } catch (Exception $e) {
            echo $class_id;
            echo $e->getMessage();
        }
    }

    public function AddStudent($class_id = '338747', $team_id = '66bdfc53-a130-4870-83a3-1bd82d04b5dc')
    {

        $students = DB::table('enrollments')->where('class_id', '=', $class_id)->whereNull('add_success')->get();
        $access_token = $this->getAccessTokenDatabase();

        foreach ($students as $student) {
            $student_mail = $student->student_mail;

            //CALL API TEAM
            $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
            $student_mail = "https://graph.microsoft.com/v1.0/users/" . $student_mail;

            $response = Http::withToken($access_token)->post($url, [
                "@odata.id" => $student_mail,
            ]);

            if ($response->successful()) {
                // Success
                DB::table('enrollments')->where('id', '=', $student->id)->update([
                    'add_success' => "success",
                ]);
                echo $student->student_mail . " : success \n";
            } else {
                $error = $response->json();
                $message = $error['error']['message'];
                DB::table('enrollments')->where('id', '=', $student->id)->update([
                    'add_success' => $message,
                ]);
                echo $student->student_mail . " : ." . $message . "\n";
                //Add Fail
            }
        }
        echo "class id : " . $class_id;
        MjuClass::where('class_id', '=', $class_id)->update([
            'add_student' => "success",
        ]);
    }

    public function AddInstructor($class_id, $team_id)
    {
        $access_token = $this->getAccessTokenDatabase();

        $instructors = DB::table('instructors')->where('class_id', '=', $class_id)->whereNull('add_success')->get();
        foreach ($instructors as $item) {
            $instructor_mail = $item->email;
            $instructor_mail = strtolower($instructor_mail);
            $instructor_mail = trim($instructor_mail);

            $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';
            $instructor_mail = "https://graph.microsoft.com/v1.0/users/" . $instructor_mail;
            $response = Http::withToken($access_token)->post($url, [
                "@odata.id" => $instructor_mail,

            ]);
            if ($response->successful()) {
                // Success
                DB::table('instructors')->where('id', '=', $item->id)->update([
                    'add_success' => "success",
                ]);
            } else {
                //Fail
                $error = $response->json();
                $message = $error['error']['message'];
                DB::table('instructors')->where('id', '=', $item->id)->update([
                    'add_success' => $message,
                ]);
            }
        }
    }

    public function getGroupmail($team_id , $class_id , $access_token)
    {
        // $access_token = $this->getAccessTokenDatabase();
        $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
        $endpoint = str_replace(" ", "", $endpoint);
        $response = Http::withToken($access_token)->get($endpoint);
        if ($response->successful()) {
            $mail = $response->json();
            DB::table('class')->where('class_id', '=', $class_id)->update([
                'group_mail' => $mail['mail'],
            ]);
            echo "Get Group Mail : Success\n";
            return $mail['mail'];
        } else {
            $error_message = $response->json()['error']['message'] ?? 'Unknown error occurred';
            DB::table('class')->where('class_id', '=', $class_id)->update([
                'group_mail' => $error_message,
            ]);
            echo "Get Group Mail : Fail" . $error_message . "\n";
        }
    }

    public function getChannel($team_id, $class_id, $access_token)
    {
        // $access_token = $this->getAccessTokenDatabase();
        $endpoint = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/channels";
        $endpoint = str_replace(" ", "", $endpoint);
        $response = Http::withToken($access_token)->get($endpoint);
        if ($response->successful()) {
            $channel_id = $response->json();
            $channel_id = $channel_id['value'][0]['id'];
            DB::table('class')->where('class_id', '=', $class_id)->update([
                'channel_id' => $channel_id,
            ]);
            echo "Get Channel ID : Success\n";
            return $channel_id;
        } else {
            $error_message = $response->json()['error']['message'] ?? 'Unknown error occurred';
            DB::table('class')->where('class_id', '=', $class_id)->update([
                'channel_id' => $error_message,
            ]);
            echo "Get Channel ID : Fail : " . $error_message . "\n";
        }
    }

    public function CreateEvent($class_id)
    {
        $all_class = DB::table('class')->where('class_id', '=', $class_id)->get();
        foreach ($all_class as $class) {

            try {

                $team_id = $class->team_id;
                $class_id = $class->class_id;
                $team_name = $class->team_name;
                $group_mail = $class->group_mail;
                $channel_id = $class->channel_id;
                // $group_mail = $this->getGroupmail($team_id, $class_id);
                // $channel_id = $this->getChannel($team_id, $class_id);

                $start_date = '2023-07-03';
                $end_date = '2023-11-06';

                // $days_of_week = [];
                $start_time = $class->start_time;
                $dulation_time = $class->duration_time;
                $study_time = $this->calculateEndTime($start_time, $dulation_time);
                $start_date_time = $start_date . 'T' . $study_time['start_time'];
                $end_date_time = $start_date . 'T' . $study_time['end_time'];

                $text_time = $study_time['start_time'] . "-" . $study_time['end_time'];

                if ($class->week_of_day == null) {
                    echo "Error\n";
                    return 0;
                }
                $day = strtoupper($class->week_of_day);

                if (!isset($this->week_of_day[$day])) {
                    echo "Error2\n";
                    return 0;
                }

                $days_of_week = $this->week_of_day[$day];
                $data = [

                    "subject" => $class->calendar_subject,
                    "body" => [
                        "contentType" => "html",
                        "content" => $class->study_type . " : " . $class->week_of_day . " : " . $text_time,
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
                        "displayName" => $class->room_name,
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

                //OWNER ACCESS TOKEN
                // $token = env('TOKEN');
                $token = $this->nun();
                if ($token === false) {
                    return 0;
                }

                $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id . "/calendar/events";

                $response = Http::withToken($token)->post($endpoint, $data);
                $response_data = $response->json();

                if (isset($response_data['error'])) {
                    DB::table('class')->where('class_id', '=', $class_id)->update([
                        'add_event' => $response_data['error'],
                    ]);
                    echo $response_data['error'] . "\n";
                    //Delete All Job
                    DB::table('jobs')->truncate();
                } else {
                    echo "Add Success : " . $class->class_id . "\n";
                    //Create Success
                    $event_id = $response['id'];
                    $body_content = $response['body'];
                    DB::table('class')->where('id', '=', $class->id)->update([
                        'event_id' => $event_id,
                        'event_body' => json_encode($body_content),
                        'add_event' => 'success',
                    ]);
                    $post_result = $this->postMeetingToTeam($team_id, $channel_id, $body_content, $team_name);
                    if ($post_result === true) {
                        DB::table('class')->where('id', '=', $class->id)->update([
                            'add_post' => 'success',
                        ]);
                    } else {
                        DB::table('class')->where('id', '=', $class->id)->update([
                            'add_post' => $post_result,
                        ]);
                    }
                }
            } catch (Exception $e) {
                DB::table('class')->where('class_id', '=', $class_id)->update([
                    'add_event' => $e->getMessage(),
                ]);
                Log::error("error", [
                    'message' => $e->getMessage(),
                    'class_id' => $class_id,
                ]);
            }
        }
    }

    public function deleteAllGroup($team_id, $class_id, $access_token)
    {
        // $access_token = $this->getAccessToken();
        $end_point = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
        $response = Http::withToken($access_token)->delete($end_point);

        DB::beginTransaction();
        DB::table('class')->where('class_id', '=', $class_id)->update([
            'team_id' => null,
            'add_student' => null,
            'add_event' => null,
            'add_instructor' => null,
        ]);
        DB::commit();
    }

    public function deleteTeamAndDatabase($team_id, $class_id)
    {
        $access_token = $this->getAccessToken();
        $end_point = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
        $response = Http::withToken($access_token)->delete($end_point);

        DB::beginTransaction();
        DB::table('class')->where('class_id', '=', $class_id)->delete();
        DB::commit();
    }

    public function postMeetingToTeam($team_id, $channel_id, $body_content, $team_name)
    {
        $access_token = $this->nun();
        $end_point = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/channels/" . $channel_id . "/messages";
        $data = [
            "subject" => $team_name,
            "body" => $body_content,
        ];
        $response = Http::withToken($access_token)->post($end_point, $data);

        if ($response->successful()) {
            echo "POST : SUCCESS\n";
            return true;
        } else {
            $error_message = $response->json()['error']['message'] ?? 'Unknown error occurred';
            echo "POST : FAIL : " . $error_message . "\n";
            return $error_message;
        }
    }

    //----------------------------------------- QUEUE -----------------------------------------------//

    public function processQueueDeleteAllTeam()
    {
        $all_class = DB::table('class')->whereNotNull('team_id')->groupBy('class_id')->get();
        $access_token = $this->getAccessToken();
        foreach ($all_class as $class) {
            dispatch(new DeleteAllTeam($class->class_id, $class->team_id, $access_token));
        }
    }

    public function processQueueCreateTeam()
    {
        $all_class = MjuClass::whereNull('team_id')->groupBy('class_id')->get();
        // dd($all_class);
        foreach ($all_class as $class) {
            try {
                $team_name = $class->team_name;
                $class_id = $class->class_id;
                $description = "";
                dispatch(new CreateTeam($team_name, $class_id, $description));
            } catch (Exception $e) {
                echo $e->getMessage();
                echo $class_id . "<br>";
            }

            // $this->createTeams($team_name, $section_id, $description);
        }
    }

    public function porcessQueueCreateEvent()
    {
        $all_class = DB::table('class')->select('class_id', 'team_id')
            ->whereNotNull('team_id')
            ->whereNull('add_event')
            ->groupBy('class_id')
            ->limit(100)
            ->get();
        // dd($all_class);
        foreach ($all_class as $class) {
            \dispatch(new CreateEventJob($class->class_id));
        }
    }

    public function processQueueAddStudent()
    {
        // dispatch(new AddStudentJob(337152, '221d70ec-bea1-485b-91b6-7f6c7d07f0da'));
        $all_class = DB::table('view_students')->get();
        foreach ($all_class as $class) {
            $class_id = $class->class_id;
            $team_id = $class->team_id;
            dispatch(new AddStudentJob($class_id, $team_id));
        }
    }
    public function processQueueAddInstructor()
    {
        // $model = new MjuClass();
        // $all_class = $model->getClassInstructorNull();
        $all_class = DB::table('view_ins')->get();

        foreach ($all_class as $class) {
            $class_id = $class->class_id;
            $team_id = $class->team_id;
            dispatch(new AddInstructorJob($class_id, $team_id));
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

    public function deleteAllEvent($team_id)
    {
        $events = DB::table('class')->whereNotNull('event_id')->get();
        dd($events);
        foreach ($events as $event) {

            $token = env('TOKEN');
            $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id . "/events/" . $event->event_id;

            $response = Http::withToken($token)->delete($endpoint);
            $json = $response->json();

            if (isset($json['error'])) {
                echo $event->event_id . "<br><br>";
            } else {
                dd($json);
            }
        }
    }
    public function test()
    {
        $groupId = "8289e2af-7dce-48ad-accb-e98b65910d32";
        $accessToken = env('TOKEN');

        $groupCalendarUrl = "https://graph.microsoft.com/v1.0/groups/$groupId/calendar";
        $groupCalendarResponse = Http::withToken($accessToken)->get($groupCalendarUrl);
        $groupCalendarData = $groupCalendarResponse->json();
        $groupCalendarId = $groupCalendarData["id"];

        // Get the events from the group's calendar
        $groupEventsUrl = "https://graph.microsoft.com/v1.0/groups/$groupId/calendar/events";
        $groupEventsResponse = Http::withToken($accessToken)->get($groupEventsUrl);
        $groupEventsData = $groupEventsResponse->json();
        $groupEvents = $groupEventsData["value"];

        // Display the group events on the member's calendar
        $memberEventsUrl = "https://graph.microsoft.com/v1.0/me/calendar/events";

        foreach ($groupEvents as $event) {
            $memberEventResponse = Http::withToken($accessToken)->post($memberEventsUrl, $event);
            $memberEventData = $memberEventResponse->json();
            $memberEventStatusCode = $memberEventResponse->status();

            if ($memberEventStatusCode === 201) {
                echo "Event added to member's calendar.\n";
            } else {
                echo "Error adding event: $memberEventStatusCode\n";
            }
        }

        foreach ($groupEvents as $event) {
            // Compare the event properties to check for a match
            $existingEventResponse = Http::withToken($accessToken)->get($memberEventsUrl, [
                '$filter' => "subject eq '{$event['subject']}' and start/dateTime eq '{$event['start']['dateTime']}' and end/dateTime eq '{$event['end']['dateTime']}'",
            ]);

            $existingEventData = $existingEventResponse->json();
            if (!empty($existingEventData['value'])) {
                $existingEventIds[] = $existingEventData['value'][0]['id'];
            } else {
                $memberEventResponse = Http::withToken($accessToken)->post($memberEventsUrl, $event);
                $memberEventData = $memberEventResponse->json();
                $memberEventStatusCode = $memberEventResponse->status();

                if ($memberEventStatusCode === 201) {
                    echo "Event added to member's calendar.\n";
                } else {
                    echo "Error adding event: $memberEventStatusCode\n";
                }
            }
        }
    }

    public function addStudentToTean()
    {
        $students = DB::table('enrollments')->whereNull('add_success')->groupBy('class_id')->get();

        foreach ($students as $student) {
            $class_detail = DB::table('class')->where('class_id', '=', $student->class_id)->get();
            $student_detail = DB::table('enrollments')->whereNull('add_success')->where('class_id', '=', $student->class_id)->get();

            if (count($class_detail) == 0) {
                //ไม่มีห้องเรียน
                dd($class_detail);
            }
        }
    }

    public function addMe()
    {
        $access_token = $this->getAccessTokenDatabase();
        $team_id = '410b69b7-c0ef-4444-83ad-b5004e440b26';
        $instructor_mail = 'prasert_kb@mju.ac.th';
        $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';
        $instructor_mail = "https://graph.microsoft.com/v1.0/users/" . $instructor_mail;
        $response = Http::withToken($access_token)->post($url, [
            "@odata.id" => $instructor_mail,

        ]);
    }

    public function postMessageToTeam($team_id, $class_id)
    {
        $access_token = env('TOKEN');
        $channel_id = $this->getChannel($team_id, $class_id);
        $end_point = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/channels/" . $channel_id . "/messages";
        $data = [
            "subject" => "!! ประกาศ !!",
            // "body" => $body_content,
            "body" => [
                "content" => "ห้องเรียน Microsoft Team ห้องนี้สร้างขึ้นเพื่อรองรับการเรียนออนไลน์ ในขณะนี้อยู่ระหว่างการเพิ่มอาจารย์เข้ามาในห้องเรียน",
            ],
        ];

        $response = Http::withToken($access_token)->post($end_point, $data);

        if ($response->successful()) {
        } else {
            echo "Error\n";
        }
    }

    public function processQueuePostMessageToTeam()
    {

        $all_class = DB::table('class')->where('created_at', 'like', '2023-07-03%')->groupBy('class_id')->get();

        foreach ($all_class as $class) {
            $team_id = $class->team_id;
            $class_id = $class->class_id;
            dispatch(new PostMessageToTeam($team_id, $class_id));
        }
    }

    public function removeStudentFromTeam($class_id)
    {
        $token = $this->getAccessTokenDatabase();
        $class = DB::table('class')->where('class_id', '=', $class_id)->first();
        if ($class == null) {
            DB::table('drops')->where('class_id', '=', $class_id)->update([
                'remove_success' => "class id null",
            ]);
            return 0;
        }
        
        $team_id = $class->team_id;
        $getMembersUrl = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/members";
        $response = Http::withToken($token)->get($getMembersUrl);
        $members = $response->json()['value'] ?? [];
        $memberId = null;

        $students = DB::table('drops')->where('class_id', '=', $class_id)->whereNull('remove_success')->get();

        foreach ($students as $student) {
            $student_mail = $student->student_mail;
            $id = $student->id;
            foreach ($members as $member) {
                if (strtolower($member['email']) === strtolower($student_mail)) {
                    $memberId = $member['id'];
                    $removeMemberUrl = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/members/" . $memberId;
                    $response = Http::withToken($token)->delete($removeMemberUrl);
                    if ($response->status() === 204) {
                        DB::table('drops')->where('id', '=', $id)->update([
                            'remove_success' => "success",
                        ]);
                        echo $team_id . ":" . $student_mail . "\n";
                    } else {
                        DB::table('drops')->where('id', '=', $id)->update([
                            'remove_success' => json_encode($response->json()),
                        ]);
                    }
                }
            }
        }
    }

    public function getGroupMailAndChannelId()
    {
        $all_class = DB::table('class')->whereNull('group_mail')->groupBy('class_id')->get();
        foreach ($all_class as $class) {
            dispatch(new GetGroupMailAndChannelIdJob($class->class_id, $class->team_id));
        }
    }

    public function nun()
    {
        $url = "https://login.microsoftonline.com/" . env('TENANT_ID') . "/oauth2/v2.0/token";
        $response = Http::asForm()->post($url, [

            'grant_type' => 'password',
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'scope' => 'https://graph.microsoft.com/.default',
            'username' => env('MAIL'),
            'password' => env('MAIL_PASS'),
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            return $token;
        } else {
            return false;
        }
    }

    public function RemoveEvent($class_id)
    {
        $events = DB::table('class')->where('class_id', '=', $class_id)->get();
        dd($events);
        foreach ($events as $event) {

            $token = env('TOKEN');
            $endpoint = "https://graph.microsoft.com/v1.0/me/events/". $event->event_id;
            
            $response = Http::withToken($token)->delete($endpoint);
            $json = $response->json();

            if (isset($json['error'])) {
                echo $event->event_id . "<br><br>";
            } else {
                dd($json);
            }
        }
       
    }
}
