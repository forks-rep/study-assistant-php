<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Faker;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Test Data Controller is only used to 
 * populate databases for testing purposes.
 * It responds to a specific API call
 */
class TestDataController extends Controller
{
    public $user;
    public $request;
    
    /**
     * Create function
     * 
     * This function obtains data from a CSV file
     * and uses this data to populate the databases
     * with test data
     *
     * @param Request $request Request object received via API POST
     * 
     * @return Response
     */
    public function create(Request $request)
    {
        $faker = Faker\Factory::create();

        $pathToCsv = \storage_path('app/public/data.csv');

        $csv = fopen($pathToCsv, 'r');
        $userArr = [];
        $flag = true;
        while (($line = fgetcsv($csv)) !== false) {
            if ($flag) {
                $flag = false;
                continue;
            }
            array_push($userArr, $line);
        }

        $userCount = 0;
        $moduleCount = 0;
        $modules = [
            'IT',
            'History',
            'Science',
            'English',
            'French',
            'Law',
            'Sinhala',
            'Biology',
            'Chemistry',
            'Physics',
            'Mathematics',
            'Statistics'
        ];

        foreach ($userArr as $value) {
            $name = $value[1];
            if ($name == '') {
                $name = $faker->name;
            }

            $uni = $value[2];
            if ($uni == '') {
                $uni = 'University of '.$faker->city;
            }

            $major = $value[3];
            if ($major == '') {
                $major = 'null';
            }

            $user = User::create(
                [
                    'name' => $name,
                    'email' => $faker->unique()->safeEmail,
                    'email_verified_at' => now(),
                    'birth' => $faker->year($max = 'now'),
                    'gender' => $faker->randomElement($array = array('M','F')),
                    'country' => 'Sri Lanka',
                    'university' => $uni,
                    'major' => $major,
                    'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                    'remember_token' => str_random(10),
                ]
            );

            $userCount++;

            for ($x = 0; $x < 6; $x++) {
                $key = (4*$x);
                if (isset($value[4 + $key]) && $value[4 + $key] != '') {
                    $module_name = $value[4 + $key];
                    $total_sessions = $value[6 + $key] * 26; //26 weeks per semester
                
                    $grade = $value[7 + $key];
                    $good_grades = array('A+', 'A', 'A-', 'B+', 'B', 'B-');

                    if (in_array($grade, $good_grades)) {
                        $completed_sessions = intval(($total_sessions*90)/100);
                        $failed_sessions = intval(($total_sessions*10)/100);
                    } else {
                        $completed_sessions = intval(($total_sessions*30)/100);
                        $failed_sessions = intval(($total_sessions*70)/100);
                    }

                    $completed_module = $user->completed_modules()->create(
                        [
                            'name' => $faker->randomElement($array = $modules),
                            'rating' =>$value[5 + $key],
                            'grade' => $grade,
                            'completed_sessions' => $completed_sessions,
                            'failed_sessions' => $failed_sessions
                        ]
                    );

                    $moduleCount++;
                }
            }
        }

        return response()->json(
            [
                'Users created' => $userCount,
                'Modules created' => $moduleCount
            ]
        );
    }
}
