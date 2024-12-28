<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Throwable;
use DB;
use Session;

class FilterController extends BaseController
{
    public function FilterParameterList(Request $request, $type): JsonResponse
    {
        try {
            //Fetch Location Lists
            $locationListing = [];
            if($type == "course"){
                $locationListing = DB::table('courses')
                                // ->select('courses.id as course_id','users.id as user_id','cities.name as label', 'cities.id as value')
                                ->select('cities.id as id', 'cities.name as label', 'cities.name as value')
                                ->join('users','users.id','courses.user_id')
                                ->join('cities','cities.id','users.city')
                                ->join('states','states.id','cities.state_id')
                                ->join('country','country.id','states.country_id')
                                ->where('courses.status','Approved')
                                ->distinct()
                                ->orderBy('cities.name','asc')
                                ->get();
            }
            else if($type == "institute"){
                $locationListing = DB::table('users')
                                ->select('cities.id as id', 'cities.name as label', 'cities.name as value')
                                ->join('cities','cities.id','users.city')
                                ->join('states','states.id','cities.state_id')
                                ->join('country','country.id','states.country_id')
                                ->where('user_type','institute')
                                ->where('users.status','Approved')
                                ->distinct()
                                ->orderBy('cities.name','asc')
                                ->get();
            }
            else if($type == "trainer"){ //trainer = tutor
                $locationListing = DB::table('users')
                                ->select('cities.id as id', 'cities.name as label', 'cities.name as value')
                                ->join('cities','cities.id','users.city')
                                ->join('states','states.id','cities.state_id')
                                ->join('country','country.id','states.country_id')
                                ->where('user_type','tutor')
                                ->where('users.status','Approved')
                                ->distinct()
                                ->orderBy('cities.name','asc')
                                ->get();
            }
            else if($type == "webinar"){ //trainer = tutor
                $locationListing = DB::table('webinars')
                                // ->select('webinars.id as webinar_id','users.id as user_id','cities.name as label', 'cities.id as value')
                                ->select('cities.id as id', 'cities.name as label', 'cities.name as value')
                                ->join('users','users.id','webinars.user_id')
                                ->join('cities','cities.id','users.city')
                                ->join('states','states.id','cities.state_id')
                                ->join('country','country.id','states.country_id')
                                ->where('webinars.status','Approved')
                                ->distinct()
                                ->orderBy('cities.name','asc')
                                ->get();
            }


            //Teaching Mode Lists
            $teachingModeLising = [
                                    ["id"=>1, "label"=> "Online", "value"=> "Online"],
                                    ["id"=>2, "label"=> "Offline", "value"=> "Offline"],
                                    ["id"=>3, "label"=> "Both", "value"=> "Both"],
                                ];

            $batchTypeLising = [
                                    ["id"=>1, "label"=> "Weekday", "value"=> "Weekday"],
                                    ["id"=>2, "label"=> "Weekend", "value"=> "Weekend"],
                                    ["id"=>3, "label"=> "Both", "value"=> "Both"],
                                ];

            $data = (object)[
                'locations' => $locationListing,
                'teachingModes' => $teachingModeLising,
                'batchTypes' => $batchTypeLising
            ];

            // Return the response
            return $this->sendSuccessResponse('Filter parameter list fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Filter parameter listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
