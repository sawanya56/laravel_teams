<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MsController extends Controller
{
    private $token = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6ImdBVVZZaTlGUUJWVjFiZUZVNGJMRkpiX0RENnFVSzVqbXJhNV9Pc0lZS1kiLCJhbGciOiJSUzI1NiIsIng1dCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyIsImtpZCI6Ii1LSTNROW5OUjdiUm9meG1lWm9YcWJIWkdldyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC84ZWM3NGEzOS1kZGY2LTQxZTEtYjBhMi1mZjA0NTllYThlYjgvIiwiaWF0IjoxNjg3NDIxODEyLCJuYmYiOjE2ODc0MjE4MTIsImV4cCI6MTY4NzQyNjg2OSwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFUUUF5LzhUQUFBQUNXWnp0Y1lTYjg5Tjl5dXl6VW1VYko5RkdzWVlWNkxlbkc5dFFHRTJzNVJRWktPdGllWU5LdldXZ2N5RTlWKzYiLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6IkdyYXBoIEV4cGxvcmVyIiwiYXBwaWQiOiJkZThiYzhiNS1kOWY5LTQ4YjEtYThhZC1iNzQ4ZGE3MjUwNjQiLCJhcHBpZGFjciI6IjAiLCJmYW1pbHlfbmFtZSI6IuC4geC4uOC4peC4muC4o-C4tOC4hOC4uOC4myIsImdpdmVuX25hbWUiOiLguJvguKPguLDguYDguKrguKPguLTguJAiLCJpZHR5cCI6InVzZXIiLCJpcGFkZHIiOiIyMDIuMjguMzguMjQ4IiwibmFtZSI6IuC4m-C4o-C4sOC5gOC4quC4o-C4tOC4kCAg4LiB4Li44Lil4Lia4Lij4Li04LiE4Li44LibIiwib2lkIjoiYjZmNDQ2ZmUtODQyNi00MzgzLTk5ZjktODFhM2I0YjJmMDE5Iiwib25wcmVtX3NpZCI6IlMtMS01LTIxLTc5MDUyNTQ3OC0xMDc4MDgxNTMzLTgzOTUyMjExNS02ODk1MjYiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDAzOTI0MjA0MCIsInJoIjoiMC5BVk1BT1VySGp2YmQ0VUd3b3Y4RVdlcU91QU1BQUFBQUFBQUF3QUFBQUFBQUFBQlRBT28uIiwic2NwIjoiQ2FsZW5kYXJzLlJlYWRXcml0ZSBDaGFubmVsLlJlYWRCYXNpYy5BbGwgQ2hhbm5lbE1lc3NhZ2UuU2VuZCBDaGF0LlJlYWQgQ2hhdC5SZWFkQmFzaWMgQ2hhdC5SZWFkV3JpdGUgQ2hhdE1lc3NhZ2UuU2VuZCBDb250YWN0cy5SZWFkV3JpdGUgRGV2aWNlTWFuYWdlbWVudFJCQUMuUmVhZC5BbGwgRGV2aWNlTWFuYWdlbWVudFNlcnZpY2VDb25maWcuUmVhZC5BbGwgRmlsZXMuUmVhZFdyaXRlLkFsbCBHcm91cC5SZWFkV3JpdGUuQWxsIElkZW50aXR5Umlza0V2ZW50LlJlYWQuQWxsIE1haWwuUmVhZCBNYWlsLlJlYWRXcml0ZSBNYWlsYm94U2V0dGluZ3MuUmVhZFdyaXRlIE5vdGVzLlJlYWRXcml0ZS5BbGwgb3BlbmlkIFBlb3BsZS5SZWFkIFBsYWNlLlJlYWQgUHJlc2VuY2UuUmVhZCBQcmVzZW5jZS5SZWFkLkFsbCBQcmludGVyU2hhcmUuUmVhZEJhc2ljLkFsbCBQcmludEpvYi5DcmVhdGUgUHJpbnRKb2IuUmVhZEJhc2ljIHByb2ZpbGUgUmVwb3J0cy5SZWFkLkFsbCBTaXRlcy5SZWFkV3JpdGUuQWxsIFRhc2tzLlJlYWRXcml0ZSBUZWFtLkNyZWF0ZSBUZWFtLlJlYWRCYXNpYy5BbGwgVXNlci5SZWFkIFVzZXIuUmVhZEJhc2ljLkFsbCBVc2VyLlJlYWRXcml0ZSBVc2VyLlJlYWRXcml0ZS5BbGwgZW1haWwiLCJzdWIiOiJGOUVMWUJ6aUhVS2FxbVgyYTk5RU9nWVNleUcxR1JuOFZfTVZPb3R1UEg0IiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IkFTIiwidGlkIjoiOGVjNzRhMzktZGRmNi00MWUxLWIwYTItZmYwNDU5ZWE4ZWI4IiwidW5pcXVlX25hbWUiOiJwcmFzZXJ0X2tiQG1qdS5hYy50aCIsInVwbiI6InByYXNlcnRfa2JAbWp1LmFjLnRoIiwidXRpIjoiSm5mLTZheWNfMFN4RkFDMFlGY0FBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19jYyI6WyJDUDEiXSwieG1zX3NzbSI6IjEiLCJ4bXNfc3QiOnsic3ViIjoid2J6SkpRaTZpUVNlLWM4ZmhhNzZIbThncnMyejM2cGd6NWpLa2RvWm9aSSJ9LCJ4bXNfdGNkdCI6MTM5MzIxNDY5M30.JpbG8ZfcBH7i1QmwNi0Fqky0JgWLmo4DqkE2mvhuKAPMzd_5bH7mqiHFlaD047HCb95-6LKazbTQcaK9--SXDyM2HO5KpBBoeC2VvfNE89b7_wKdh4HUv7k5pHVIgV4PtTqDLfM23OCHqJ2ddF5DakHag9cymB8DCEIg569Ewtkg6uFn6YKWUN8PClfZwQolsI1KYxFhGu1PQa6aUMtOanftM5QWWTTp-VAukXbMSseZEC4JdbC8CG3Ikqu4xWrrxNsGRioMG6J3Uxwj3QIWrxq_CM8gMInIa9SDyJXwHYVM3FToS_nPlr3NRuftKdtMVsPoX0t-V6TF0W8O75kehw';

    public function Main()
    {

        $sections = DB::table('view_sections')->limit(2)->get();

        foreach ($sections as $section) {

            $team_name = $section->team_name;
            $section_id = $section->section;
            $description = $section->description;

            //Create Room and return Team ID
            $team_id = $this->CreateTeams($team_name, $description, $section_id);

            $this->AddStudent($section_id, $team_id);
            $this->AddInstructor($section_id, $team_id);
            $this->getGroupmail($team_id, $section_id);

            $data = [
                'team_id' => $team_id,
                // 'start_date' => $section->
            ];
            $this->CreateEvent($data);
        }
    }

    public function CreateTeams($team_name, $description, $section_id)
    {
        $response = Http::withToken($this->token)->post('https://graph.microsoft.com/v1.0/teams', [
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

        return $ms_team_id;
    }

    public function AddStudent($section_id = 333146, $team_id = 'bd5f3682-eb31-4b17-8bdd-4897183af1b0')
    {
        // dd($this->token);
        $students = DB::table('view_students')->where('section', '=', $section_id)->get();

        foreach ($students as $student) {
            $student_mail = $student->student_mail;
            echo $section_id . " : " . $student_mail . "<br>";
            $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/members/$ref';
            //CALL API TEAM
            // dd($url);
            // $student_mail = "https://graph.microsoft.com/v1.0/users('".$student_mail."')";
            // dd($student_mail);
            
            $student_mail = "https://graph.microsoft.com/v1.0/users('mju6204101356@mju.ac.th')";
            $response = Http::withToken($this->token)->post($url, [
                "@odata.id" => $student_mail,
        
            ]);
            dd($response);
            //END CALL API
        }

    }

    public function AddInstructor($section_id = 333146, $team_id = null)
    {
        $instructors = DB::table('view_instructors')->where('section', '=', $section_id)->get();
        // dd($instructors);
        foreach ($instructors as $item) {
            $instructor_mail = $item->instructor_mail;
            // dd($item, $instructor_mail);
            $url = 'https://graph.microsoft.com/v1.0/groups/' . $team_id . '/owners/$ref';

            $instructor_mail =  "https://graph.microsoft.com/v1.0/users('mju6204101356@mju.ac.th')";
            $response = Http::withToken($this->token)->post($url, [
                "@odata.id" => $instructor_mail,
        
            ]);
        }
    }

    public function getGroupmail($team_id ="bd5f3682-eb31-4b17-8bdd-4897183af1b0",$section_id = 336028)
    {
        $endpoint = "https://graph.microsoft.com/v1.0/groups/ ".$team_id; 
        $endpoint = str_replace(" ","",$endpoint);
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
}
