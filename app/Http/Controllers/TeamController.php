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
    public $week_of_day = [
        'MO' => "monday",
        'TU' => "tuesday",
        'WE' => "wednesday",
        'TH' => "thursday",
        'FR' => "friday",
        'SA' => "saturday",
        'SU' => "sunday",

    ];


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

    public function addInstructor($class_id, $team_id, $instructor_mail)
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

    public function addStudent($class_id, $team_id, $student_id)
    {
        $model = new Enrollment();

        if ($model->studentEnrollExist($class_id, $student_id)) {
            return true;
        }

        $access_token = parent::getAccessToken();
        $student_mail = 'mju' . $student_id . '@mju.ac.th';
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

        $model->updateStudentStatus($student_id, $class_id, $student_status);
        return $success;
    }

    public function removeStudent($team_id, $student_mail, $class_id)
    {
        $token = parent::getAccessToken();

        $getMembersUrl = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/members";
        $response = Http::withToken($token)->get($getMembersUrl);
        $members = $response->json()['value'] ?? [];
        $memberId = null;

        foreach ($members as $member) {
            if (strtolower($member['email']) === strtolower($student_mail)) {
                $memberId = $member['id'];
                $removeMemberUrl = "https://graph.microsoft.com/v1.0/teams/" . $team_id . "/members/" . $memberId;
                $response = Http::withToken($token)->delete($removeMemberUrl);
                if ($response->successful()) {
                    $enroll = DB::table('enrollments')->where([
                        ['class_id', '=', $class_id],
                        ['student_mail', '=', $student_mail],
                    ])->delete();

                    return true;
                } else {
                    $message = $response->json();
                    return false;
                }
                break;
            }
        }

        return false;
    }

    public function createEvent($class_id)
    {
        $all_class = DB::table('class')->where('class_id', '=', $class_id)->get();
        foreach ($all_class as $class) {
            $access_token = parent::getAccessToken();
            try {
                $team_id = $class->team_id;
                $class_id = $class->class_id;
                $team_name = $class->team_name;
                $group_mail = $class->group_mail;
                $channel_id = $class->channel_id;
                $group_mail = $this->getGroupmail($team_id, $class_id,$access_token);
                $channel_id = $this->getChannel($team_id, $class_id,$access_token);

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
                    return false;
                }
                $day = strtoupper($class->week_of_day);

                if (!isset($this->week_of_day[$day])) {
                    echo "Error2\n";
                    return false;
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

                $token = parent::getAccessToken();
                if ($token === false) {
                    return false;
                }

                $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id . "/calendar/events";

                $response = Http::withToken($token)->post($endpoint, $data);
                $response_data = $response->json();

                if (isset($response_data['error'])) {
                    MjuClass::where('class_id', '=', $class_id)->update([
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
                    MjuClass::where('id', '=', $class->id)->update([
                        'event_id' => $event_id,
                        'event_body' => json_encode($body_content),
                        'add_event' => 'success',
                    ]);
                    $post_result = $this->postMeetingToTeam($team_id, $channel_id, $body_content, $team_name);
                    if ($post_result === true) {
                        MjuClass::where('id', '=', $class->id)->update([
                            'add_post' => 'success',
                        ]);
                    } else {
                        MjuClass::where('id', '=', $class->id)->update([
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

    public function postMeetingToTeam($team_id, $channel_id, $body_content, $team_name)
    {
        $access_token = parent::getAccessToken();
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

    public function getGroupmail($team_id, $class_id, $access_token)
    {
        $endpoint = "https://graph.microsoft.com/v1.0/groups/" . $team_id;
        $endpoint = str_replace(" ", "", $endpoint);
        $response = Http::withToken($access_token)->get($endpoint);
        if ($response->successful()) {
            $mail = $response->json();
            MjuClass::where('class_id', '=', $class_id)->update([
                'group_mail' => $mail['mail'],
            ]);
            echo "Get Group Mail : Success\n";
            return $mail['mail'];
        } else {
            $error_message = $response->json()['error']['message'] ?? 'Unknown error occurred';
            MjuClass::where('class_id', '=', $class_id)->update([
                'group_mail' => $error_message,
            ]);
            // echo "Get Group Mail : Fail" . $error_message . "\n";
            return false;
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
            MjuClass::where('class_id', '=', $class_id)->update([
                'channel_id' => $channel_id,
            ]);
            echo "Get Channel ID : Success\n";
            return $channel_id;
        } else {
            $error_message = $response->json()['error']['message'] ?? 'Unknown error occurred';
            MjuClass::where('class_id', '=', $class_id)->update([
                'channel_id' => $error_message,
            ]);
            return false;
        }
    }
}
