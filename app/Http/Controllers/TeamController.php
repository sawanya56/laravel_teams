<?php

namespace App\Http\Controllers;
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
        } catch (Exception $e) {
            echo $class_id;
            echo $e->getMessage();
        }
    }
}
