<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Throwable;
use DB;
use Session;

class CourseController extends BaseController
{

    public function course_add_page(Request $request): JsonResponse
    {
        try {
            $categories = DB::table('categories')
                ->get();
            $skills = DB::table('skills')
                ->get();
            $currency = DB::table('currency')
                ->get();

            $data = (object)[
                'categories' => $categories,
                'skills' => $skills,
                'currency' => $currency
            ];

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Course details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Token generation error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function add(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'category_id' => 'required|integer',
                'skill' => 'required',
                'course_name' => 'required',
                'year_of_exp' => 'required',
                'duration_value' => 'required',
                'duration_unit' => 'required',
                'batch_type' => 'required',
                'teaching_mode' => 'required',
                'currency_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            // Save new file
            $path = public_path('uploads/course/');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $fileName = null;
            $filePath = null;
            if ($request->hasFile('course_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo')->getClientOriginalName();
                $request->course_logo->move($path, $fileName);
                $filePath = "uploads/course/" . $fileName;
            }

            // $preview_fileName = null;
            // $preview_filePath = null;
            // if ($request->hasFile('course_logo_preview')) {
            //     $preview_fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo_preview')->getClientOriginalName();
            //     $request->course_logo_preview->move($path, $preview_fileName);
            //     $preview_filePath = "uploads/course" . $preview_fileName;
            // }

            $insertedData = [
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'course_name' => $request->course_name,
                'skill_id' => $request->skill,
                'year_of_exp' => $request->year_of_exp,
                'duration_value' => $request->duration_value,
                'duration_unit' => $request->duration_unit,
                'batch_type' => $request->batch_type,
                'teaching_mode' => $request->teaching_mode,
                'fee' => $request->fee,
                'fee_unit' => $request->fee_unit,
                'currency_id' => $request->currency_id,
                'demo_video_url' => $request->demo_video_url,
                'course_content' => $request->course_content,
                'first_class_free' => $request->first_class_free,
                'course_logo' => $filePath,
                'course_logo_preview' => $request->course_logo_preview,
                'first_class_free' => $request->first_class_free,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            
            $course_id = DB::table('courses')->insertGetId($insertedData);

            if ($request->skill && count(json_decode($request->skill)) > 0) {
                $skillArr = json_decode($request->skill);
                //DB::table('courses_skills')->where('course_id', $request->course_id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'course_id' => $course_id,
                        'skill_name' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('courses_skills')->insert($insertedData);
                }
            }

            if ($request->language && count(json_decode($request->language)) > 0) {
                $skillArr = json_decode($request->language);
                //DB::table('courses_skills')->where('course_id', $request->course_id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'course_id' => $course_id,
                        'language_id' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('course_languages')->insert($insertedData);
                }
            }

            $user_details = DB::table('users')->where('id', $request->user_id)->first();

            if ($user_details) {
                $email = $user_details->email;
                $name = $user_details->f_name;

                $data = array("email" => $email, "name" => $name, 'course_name' => $request->course_name);
                // Send email
                Mail::send('email.courseListing', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Your Course Listing is Under Review on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

                $date = \Carbon\Carbon::now();
                $adminData = array("name" => $name, "course_title" => $request->course_name, "course_description" => $request->course_content, "course_fee" => $request->fee, "duration" => $request->duration_value, 'duration_unit' => $request->duration_unit, "teaching_mode" => $request->teaching_mode, "date_added" => $date);
                $adminEmail = env('ADMIN_MAIL');
                // Send email
                Mail::send('email.admin.courseAdditionAlert', $adminData, function ($message) use ($adminEmail) {
                    $message->to($adminEmail) // Use the recipient's email
                        ->subject('New Course Added by Tutor on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Course added successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Course added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function view_course_update(Request $request, $id): JsonResponse
    {
        try {
            $data = DB::table('courses')
                ->select(
                'courses.id',
                    'courses.category_id',
                    'categories.name as category_name',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.batch_type',
                    'courses.teaching_mode',
                    'courses.fee',
                    'courses.fee_unit',
                    'courses.currency_id',
                    'currency.code as currency_code',
                    'currency.symbol as currency_symbol',
                    'courses.first_class_free',
                    'courses.demo_video_url',
                    'courses.course_content',
                    'courses.course_logo',
                    'courses.course_logo_preview'
                )
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('currency', 'courses.currency_id', '=', 'currency.id')
                ->where('courses.id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }


            $skill = DB::table('courses_skills')
            ->where('course_id', $id)
            ->pluck('skill_name')
                ->toArray();


            $skills = DB::table('skills')->select('id as value', 'name as label')->whereIn('id', $skill)->get();


            $language = DB::table('course_languages')
            ->where('course_id', $id)
            ->pluck('language_id')
            ->toArray();

            $languages = DB::table('languages')->select('id as value', 'name as label')->whereIn('id', $language)->get();

            $data->skill = $skills;
            $data->language = $languages;

            return $this->sendSuccessResponse('Course details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Course details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function delete_file($path)
    {
        //$file_path = public_path('path/to/your/file.txt');
        $file_path = public_path($path);
        if (File::exists($file_path)) {
            File::delete($file_path);
            // echo 'File deleted successfully.';
        } else {
            // echo 'File does not exist.';
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            //dd($request->all());

            $validator = Validator::make($request->all(), [
                //'course_id' => 'required|integer',
                'category_id' => 'required|integer',
                'skill' => 'required',
                'course_name' => 'required',
                'year_of_exp' => 'required',
                'duration_value' => 'required',
                'duration_unit' => 'required',
                'batch_type' => 'required',
                'teaching_mode' => 'required',
                'currency_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $courseData = DB::table('courses')
                ->select(
                    'users.f_name',
                    'courses.category_id',
                    'courses.course_name',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.batch_type',
                    'courses.teaching_mode',
                    'courses.fee',
                    'courses.course_content',
                    'courses.user_id',
                    'courses.course_logo',
                    'courses.course_logo_preview'
                )
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->where('courses.id', $id)
                ->first();

            if (!$courseData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }


            $user_id = Auth::user()->id;
            if ($user_id != $courseData->user_id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }
            // Save new file
            $path = public_path('uploads/course/');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $updatedData = [
                'category_id' => $request->category_id,
                'skill_id' => $request->skill,
                'course_name' => $request->course_name,
                'year_of_exp' => $request->year_of_exp,
                'duration_value' => $request->duration_value,
                'duration_unit' => $request->duration_unit,
                'batch_type' => $request->batch_type,
                'teaching_mode' => $request->teaching_mode,
                'fee' => $request->fee,
                'fee_unit' => $request->fee_unit,
                'currency_id' => $request->currency_id,
                'demo_video_url' => $request->demo_video_url,
                'course_content' => $request->course_content,
                // 'course_logo' => $filePath,
                // 'course_logo_preview' => $request->course_logo_preview,
                // 'status' => 'Pending',
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('course_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo')->getClientOriginalName();
                $request->course_logo->move($path, $fileName);
                $filePath = "uploads/course/" . $fileName;
                

                if ($courseData->course_logo) {
                    $this->delete_file($courseData->course_logo);
                }
                
                $updatedData['course_logo_preview'] = $request->course_logo_preview;
            } else {
                $filePath = $courseData->course_logo;
            }
            
            $updatedData['course_logo'] = $filePath;

            // $preview_fileName = null;
            // $preview_filePath = null;
            // if ($request->hasFile('course_logo_preview')) {
            //     $preview_fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo_preview')->getClientOriginalName();
            //     $request->course_logo_preview->move($path, $preview_fileName);
            //     $preview_filePath = "uploads/course" . $preview_fileName;

            //     if ($courseData->course_logo_preview) {
            //         $this->delete_file($courseData->course_logo_preview);
            //     }
            // } else {

            //     $preview_filePath = $courseData->course_logo_preview;
            // }

            // $updatedData = [
            //     'category_id' => $request->category_id,
            //     'skill_id' => $request->skill,
            //     'course_name' => $request->course_name,
            //     'year_of_exp' => $request->year_of_exp,
            //     'duration_value' => $request->duration_value,
            //     'duration_unit' => $request->duration_unit,
            //     'batch_type' => $request->batch_type,
            //     'teaching_mode' => $request->teaching_mode,
            //     'fee' => $request->fee,
            //     'fee_unit' => $request->fee_unit,
            //     'currency_id' => $request->currency_id,
            //     'demo_video_url' => $request->demo_video_url,
            //     'course_content' => $request->course_content,
            //     'course_logo' => $filePath,
            //     'course_logo_preview' => $request->course_logo_preview,
            //     'status' => 'Rejected',
            //     'updated_at' => \Carbon\Carbon::now(),
            // ];

            $storeInfo = DB::table('courses')->where('id', $id)->update($updatedData);

            if ($request->skill && count(json_decode($request->skill)) > 0) {
                $skillArr = json_decode($request->skill);
                DB::table('courses_skills')->where('course_id', $id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'course_id' => $id,
                        'skill_name' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('courses_skills')->insert($insertedData);
                }
            }
            if ($request->language && count(json_decode($request->language)) > 0) {
                $skillArr = json_decode($request->language);
                DB::table('course_languages')->where('course_id', $id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'course_id' => $id,
                        'language_id' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('course_languages')->insert($insertedData);
                }
            }

            $date = \Carbon\Carbon::now();
            $adminData = array(
                "user_name" => $courseData->f_name,
                "course_name" => $request->course_name,
                "course_content" => $request->course_content,
                "duration_value" => $request->duration_value,
                "duration_unit" => $request->duration_unit,
                "teaching_mode" => $request->teaching_mode,
                "fee" => $request->fee,
                "date" => $date,
                "p_course_content" => $courseData->course_content,
                "p_duration_value" => $courseData->duration_value,
                "p_duration_unit" => $courseData->duration_unit,
                "p_teaching_mode" => $courseData->teaching_mode,
                "p_fee" => $courseData->fee,
            );
            $adminEmail = env('ADMIN_MAIL');
            // Send email
            Mail::send('email.admin.courseModificationAlert', $adminData, function ($message) use ($adminEmail) {
                $message->to($adminEmail) // Use the recipient's email
                    ->subject('Course Modification Alert on FindMyGuru');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('Course updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Token generation error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function delete(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('courses')
                ->select('id','user_id', 'course_logo')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }
            
            $user_id = Auth::user()->id;
            if ($user_id != $data->user_id) {
                return $this->sendErrorResponse('Unauthorized request!', '', 401);
            }
            
            if ($data->course_logo) {
                $this->delete_file($data->course_logo);
            }

            DB::table('courses')->delete($id);
            DB::table('user_course_student_lead')->where('course_id', $id)->delete();
            DB::table('student_course_reviews')->where('course_id', $id)->delete();

            return $this->sendSuccessResponse('Course deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Course deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function listing(Request $request): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 10;
            // Get filter inputs from the request
            $rating = $request->input('rating'); // Optional filter by rating
            $location = $request->input('location'); // Optional filter by city
            $teaching_mode = $request->input('teaching_mode'); // Optional filter by teaching mode
            $batch_type = $request->input('batch_type'); // Optional filter by batch type
            $skill = $request->input('skill'); // Optional filter by skill
            $sortby = $request->input('sortby'); // Optional sorting by rating

            // Build the query
            $query = DB::table('courses')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'courses.id',
                    'courses.user_id',
                    'courses.course_name',
                    'courses.fee',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.featured',
                    'courses.course_logo',
                    DB::raw('GROUP_CONCAT(skills.name) as skills'),
                    //DB::raw('CAST(COALESCE(ROUND(AVG(student_course_reviews.rating), 1), 0) AS UNSIGNED) as average_rating')
                    //DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS FLOAT), 0) as average_rating')
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                    DB::raw('COUNT(DISTINCT user_course_student_lead.id) as contactListCount')
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('courses_skills', 'courses.id', '=', 'courses_skills.course_id')
                ->leftJoin('skills', 'skills.id', '=', 'courses_skills.skill_name')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('user_course_student_lead', 'user_course_student_lead.course_id', '=', 'courses.id')
                ->groupBy(
                    'users.f_name',
                    'city_name',
                    'category_name',
                    'courses.id',
                    'courses.user_id',
                    'courses.course_name',
                    'courses.fee',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.course_logo',
                    'courses.featured'
                );
                // ->orderBy('courses.created_at', 'desc');
                
            if (is_null($rating) && is_null($location) && is_null($teaching_mode) && is_null($batch_type) && is_null($skill) && is_null($sortby)){
                $query->orderBy('courses.created_at', 'desc');
            }

            // $query->where('courses.status', '!=', 'Rejected');
            $query->where('courses.status', 'Approved');
            // Conditionally apply filters if parameters exist

            if (!is_null($rating)) {
                $query->having('average_rating', '>=', $rating);
            }

            if (!is_null($skill)) {
                $query->where('skills.name', 'like', '%' . $skill . '%');
            }

            if (!is_null($location)) {
                $query->where(DB::raw('LOWER(cities.name)'), 'like', '%' . strtolower($location) . '%');
            }

            if (!is_null($teaching_mode)) {
                $query->where('courses.teaching_mode', '=', $teaching_mode);
            }
            
            if (!is_null($batch_type)) {
                $query->where('courses.batch_type', '=', $batch_type);
            }

            // Apply sorting if provided
            // if ($sortby == 'rating_high_to_low') {
            if ($sortby == 'rating-high') {
                $query->orderBy('average_rating', 'desc');
            }
            // elseif ($sortby == 'rating_low_to_high') {
            elseif ($sortby == 'rating-low') {
                $query->orderBy('average_rating', 'asc');
            }
            elseif ($sortby == 'price-high') {
                $query->orderBy('courses.fee', 'desc');
            }
            elseif ($sortby == 'price-low') {
                $query->orderBy('courses.fee', 'asc');
            }
            elseif ($sortby == 'popular') {
                $query->orderBy('contactListCount', 'desc');
            }
            else {
                // Default sorting (optional, if you want)
                $query->orderBy('average_rating', 'desc');
            }
            
            // Execute the query
            $courseDetails = $query->paginate($perPage, ['*'], 'page', $pageNumber);
            
            // Transform the skill_name column into an array
            $courseDetails->transform(function ($course) {
                $course->skills = $course->skills ? explode(',', $course->skills) : [];
                return $course;
            });

            if (!is_null($skill) && !is_null($location)) {
                $insertedData = [
                    "skill" => $skill,
                    "location" => $location,
                    "ip_address" => $request->ip(),
                ];
                $storeInfo = DB::table('search_enquiry')->insert($insertedData);
            }
            
            //fetch suggestion words
            $suggetionWords = [];
            if (!is_null($skill)) {
                $searchedSkillCategories = DB::table('skills')
                                                ->where('name', 'like', '%'.$skill.'%')
                                                ->distinct()
                                                ->pluck('category_id');

                $suggetionWords = DB::table('skills')
                                        ->select('id','name')
                                        ->whereIn('category_id',$searchedSkillCategories)
                                        ->where('name', '!=', $skill)
                                        ->get();
            }

            // Return the response
            // return $this->sendSuccessResponse('Courses fetched successfully.', $courseDetails);
            $response = [
                'status' => true,
                'message' => 'Courses fetched successfully.',
                'data'    => $courseDetails,
                'suggestions' => $suggetionWords,
            ];
            return response()->json($response, 200);
            
        } catch (\Throwable $th) {
            Log::error('Courses fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function userCourseListing(Request $request): JsonResponse
    {
        try {
            
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;

            // Get filter inputs from the request
            $user_id = (int)$request->input('user_id');
            $filter = $request->input('filter'); 

            if($user_id){
                $auth_user_id = Auth::user()->id;
                if ($user_id != $auth_user_id) {
                    return $this->sendErrorResponse('Authentication error.', '');
                }
            }
            
            // Build the query
            $query = DB::table('courses')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'courses.id',
                    'courses.user_id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.featured',
                    'courses.course_logo',
                    'courses.course_logo_preview',
                    'courses.status',
                    DB::raw('GROUP_CONCAT(skills.name) as skills'),
                    //DB::raw('AVG(student_course_reviews.rating) as average_rating')
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('courses_skills', 'courses.id', '=', 'courses_skills.course_id')
                ->leftJoin('skills', 'skills.id', '=', 'courses_skills.skill_name')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->groupBy(
                    'users.f_name',
                    'courses.user_id',
                    'city_name',
                    'category_name',
                    'courses.id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.course_logo',
                    'courses.course_logo_preview',
                    'courses.featured',
                    'courses.status',
                );

            // Conditionally apply filters if parameters exist
            if (!is_null($user_id)) {
                $query->where('courses.user_id', '=', $user_id);
            }

            if (!is_null($filter)) {
                $query->where('courses.status', '=', $filter);
            }
            
            // Execute the query
            $courseDetails = $query
                                ->orderBy('courses.updated_at','desc')
                                ->paginate($perPage, ['*'], 'page', $pageNumber);

            // Return the response
            return $this->sendSuccessResponse('Courses fetched successfully.', $courseDetails);
        } catch (\Throwable $th) {
            Log::error('Course listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    
    public function coursesReviewListing(Request $request, $id): JsonResponse
    {
        try {

            $course_id = $id;
            $reviews = DB::table('student_course_reviews')
            ->select('id', 'student_name', 'student_email', 'student_phone', 'rating', 'review', 'created_at')
            ->where('course_id', $course_id)
            ->where('approval_status', 'Approved')
            ->orderBy('rating', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            return $this->sendSuccessResponse('Course`s review listing fetched successfully.', $reviews);
        } catch (\Throwable $th) {
            Log::error('Course`s review listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function courseDetails(Request $request, $id): JsonResponse
    {

        try {
            // Get filter inputs from the request
            $course_id = $id; // Optional filter by rating

            // Build the query
            $query = DB::table('courses')
                ->select(
                    'users.f_name',
                    'users.profile_pic',
                    'users.bio',
                    'users.user_type',
                    'users.year_of_exp',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'courses.id',
                    'courses.user_id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.featured',
                    'courses.course_logo',
                    'courses.course_logo_preview',
                    'courses.first_class_free',
                    'courses.demo_video_url',
                    'courses.course_content',
                    'courses.fee',
                    'courses.category_id',
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                    DB::raw('COUNT(student_course_reviews.rating) as total_rating'),
                    DB::raw('COUNT(ucsl.id) as total_students')
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('user_course_student_lead as ucsl', 'ucsl.course_id', '=', 'courses.id')
                ->groupBy(
                    'users.f_name',
                    'users.profile_pic',
                    'users.bio',
                    'users.user_type',
                    'users.year_of_exp',
                    'city_name',
                    'category_name',
                    'courses.id',
                    'courses.user_id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.course_logo',
                    'courses.course_logo_preview',
                    'courses.first_class_free',
                    'courses.demo_video_url',
                    'courses.course_content',
                    'courses.featured',
                    'courses.fee',
                    'courses.category_id',

                );

            // Conditionally apply filters if parameters exist

            $query->where('courses.id', '=', $course_id);

            // Execute the query
            $courseDetails = $query->first();

            if (!$courseDetails) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $reviews = DB::table('student_course_reviews')
                ->select('student_name', 'student_email', 'student_phone', 'rating', 'review', 'created_at')
                ->where('course_id', $course_id)
                ->where('approval_status', 'Approved')
                ->orderBy('rating', 'desc')
                ->limit(2)
                ->get();
            
            
            $totalReviews = DB::table('student_course_reviews')->where('course_id', $course_id)->count('id');
            $totalStudents = DB::table('user_course_student_lead')->where('course_id', $course_id)->count('id');

            $ratingDetails = DB::table('student_course_reviews')
                ->select(DB::raw('rating as stars, COUNT(*) as count'))
                ->where('course_id', $course_id)
                ->groupBy('rating')
                ->orderBy('stars', 'desc')
                ->get();

            $ratingDetails = $ratingDetails->map(function ($item, $index) {
                return [
                    'id' => $item->stars, // or assign custom IDs if required
                    'stars' => (int) $item->stars,
                    'count' => (int) $item->count,
                ];
            });

            $allRatings = collect([5, 4, 3, 2, 1])->map(function ($star) use ($ratingDetails) {
                $existing = $ratingDetails->firstWhere('stars', $star);
                return [
                    'id' => $star,
                    'stars' => $star,
                    'count' => $existing ? $existing['count'] : 0, // Default count to 0 if no reviews
                ];
            });

            $skill = DB::table('courses_skills')
            ->where('course_id', $course_id)
            ->pluck('skill_name')
                ->toArray();

            $skills = DB::table('skills')->whereIn('id', $skill)
                ->pluck('name')
                ->toArray();

            $courseDetails->skill = $skills ?? [];

            $lang = DB::table('course_languages')
            ->where('course_id', $course_id)
            ->pluck('language_id')
            ->toArray();

            $languages = DB::table('languages')->whereIn('id', $lang)
            ->pluck('name')
            ->toArray();

            $courseDetails->languages = $languages ?? [];
            //========================
            // Build the query
            $relatedQuery = DB::table('courses')
            ->select(
                'users.f_name',
                'cities.name as city_name',
                'categories.name as category_name',
                'skills.name as skill_name',
                'courses.id',
                'courses.user_id',
                'courses.course_name',
                'courses.year_of_exp',
                'courses.duration_value',
                'courses.duration_unit',
                'courses.teaching_mode',
                'courses.batch_type',
                'courses.featured',
                'courses.course_logo',
                //DB::raw('AVG(student_course_reviews.rating) as average_rating')
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
            )
            ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
            ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
            ->leftJoin('cities', 'users.city', '=', 'cities.id')
            ->where('courses.id','!=', $course_id)
            ->groupBy(
                'users.f_name',
                'city_name',
                'category_name',
                'skill_name',
                'courses.id',
                'courses.user_id',
                'courses.course_name',
                'courses.year_of_exp',
                'courses.duration_value',
                'courses.duration_unit',
                'courses.teaching_mode',
                'courses.batch_type',
                'courses.course_logo',
                'courses.featured'
            )
            ->limit(4)
            ->orderBy('average_rating', 'desc');

            $relatedQuery->where('courses.category_id', '=', $courseDetails->category_id);
            $relatedQuery->where('courses.status', '!=', 'Rejected');
            // Execute the query
            $relatedCourses = $relatedQuery->get();
            //========================
            //========================
            $blogQuery = DB::table('blogs')
                ->select(
                    'categories.name as category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.full_content',
                    'blogs.tags',
                    'blogs.category_id',
                    'blogs.created_at',
                    DB::raw('COUNT(blog_comments.id) as no_of_comments')
                )
                ->leftJoin('blog_comments', 'blogs.id', '=', 'blog_comments.blog_id')
                ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
                ->groupBy(
                    'category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.full_content',
                    'blogs.tags',
                    'blogs.category_id',
                    'blogs.created_at',
                )
                ->limit(4);

            $blogQuery->where('blogs.category_id','=', $courseDetails->category_id);
            $relatedBlogs = $blogQuery->get();

            //========================

            $courseDetails->totalReviews = $totalReviews;
            $courseDetails->totalStudents = $totalStudents;
            $courseDetails->ratingDetails = $allRatings;
            $courseDetails->reviews = $reviews;
            $courseDetails->relatedCourses = $relatedCourses;
            $courseDetails->relatedBlogs = $relatedBlogs;

            // Return the response
            return $this->sendSuccessResponse('Course details fetched successfully.', $courseDetails);
        } catch (\Throwable $th) {
            Log::error('Course listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    public function contactWithTrainer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                
                'course_id' => 'required',
                'student_name' => 'required',
                'student_email' => 'required|email',
                'student_phone' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $course_details = DB::table('courses')
            ->select('courses.category_id', 'courses.user_id', 'users.email', 'users.f_name', 'courses.course_name')
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->where('courses.id', $request->course_id)->first();

            if (!$course_details) {
                return $this->sendErrorResponse('Data not found', '');
            }

            $insertedData = [
                'user_id' => $course_details->user_id,
                'course_id' => $request->course_id,
                'category_id' => $course_details->category_id,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone,
                'student_message' => $request->student_message,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            $storeInfo = DB::table('user_course_student_lead')->insert($insertedData);
            $email = $course_details->email;
            $data = array(
                "name" => $course_details->f_name,
                "email" => $email,
                "course_name" => $course_details->course_name,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone,
                "date" => \Carbon\Carbon::now()
            );
            // Send email
            Mail::send('email.leadGeneration', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('New Student Lead for Your Course on FindMyGuru!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            //=== admin mail
            $adminEmail = env('ADMIN_MAIL');
            // Send email
            Mail::send('email.admin.studentLeadGenerationNotification', $data, function ($message) use ($adminEmail) {
                $message->to($adminEmail) // Use the recipient's email
                    ->subject('Student Lead Generation Alert on FindMyGuru');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            //== Student mail

            $user_details = DB::table('users')->where('id', $course_details->user_id)->first();
            if ($user_details) {
                $studentData =
                    array(
                        "name" => $user_details->f_name . ($user_details->l_name ? ' ' . $user_details->l_name : ''),
                        "phone" => $user_details->phone,
                        "email" => $user_details->email,
                        "course_name" => $course_details->course_name,
                        'student_name' => $request->student_name,
                        "date" => \Carbon\Carbon::now()
                    );
                $studentEmail = $request->student_email;
                Mail::send('email.tutorContactDetails', $studentData, function ($message) use ($studentEmail) {
                    $message->to($studentEmail) // Use the recipient's email
                        ->subject('Your Tutor/Institute Contact Information from FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Trainer will contact you soon.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Contact with trainer error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function studentsReview(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required',
                'student_name' => 'required',
                'student_email' => 'required|email',
                'student_phone' => 'required',
                'rating' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $course_details = DB::table('courses')
            ->select('courses.category_id', 'courses.user_id', 'users.email', 'users.f_name', 'courses.course_name')
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->where('courses.id', $request->course_id)->first();

            if (!$course_details) {
                return $this->sendErrorResponse('Data not found', '');
            }

            $insertedData = [
                'user_id' => $course_details->user_id,
                'course_id' => $request->course_id,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone,
                'rating' => $request->rating,
                'review' => $request->review,
                'approval_status' => 'Pending',
                'date' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('student_course_reviews')->insert($insertedData);

            $user_details = DB::table('users')->where('id', $course_details->user_id)->first();

            if ($user_details) {
                $email = $user_details->email;
                $name =  $user_details->f_name;
                $data = array("email" => $email, "name" => $name, 'rating' => $request->rating, 'student_name' => $request->student_name, 'review' => $request->review, 'date' => \Carbon\Carbon::now());
                // Send email
                Mail::send('email.reviewSubmission', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('New Review/Rating Received on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

                //==== Send mail to admin
                $adminData = array("email" => $email, "name" => $name, 'rating' => $request->rating, 'student_name' => $request->student_name, 'review' => $request->review, 'date' => \Carbon\Carbon::now());
                $adminEmail = env('ADMIN_MAIL');
                // Send email
                Mail::send('email.admin.ReviewApprovalAlert', $adminData, function ($message) use ($adminEmail) {
                    $message->to($adminEmail) // Use the recipient's email
                        ->subject('Review/Rating Approval Notification on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Thank you for your review.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Review error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function userCourseLead(Request $request, $id): JsonResponse
    {
        try {

            $start_date = $request->input('start_date'); // Optional filter by start_date
            $end_date = $request->input('end_date'); // Optional filter by end_date
            $user_id = $id;

            $authUser_id = Auth::user()->id;
            if ($user_id != $authUser_id) {
                return $this->sendErrorResponse('Unauthorized request!', '', 401);
            }

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $query = DB::table('user_course_student_lead as ucsl')
                ->select('ucsl.id','courses.course_name', 'ucsl.student_name', 'ucsl.student_email', 'ucsl.student_phone', 'ucsl.student_message', 'ucsl.tutor_action', 'ucsl.tutor_notes', 'ucsl.created_at',)
                ->leftJoin('courses', 'ucsl.course_id', '=', 'courses.id')
                ->where('ucsl.user_id', $user_id);

            if (!is_null($start_date) && !is_null($end_date)) {
                $query->whereBetween('ucsl.created_at', [$start_date, $end_date]);
            }

            $studentLead = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            return $this->sendSuccessResponse('User students lead fetch successfully.', $studentLead);
        } catch (\Throwable $th) {
            Log::error('User lead fetch error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function updateRemarks(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [

                'course_lead_id' => 'required',
                'tutor_notes' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $lead_details = DB::table('user_course_student_lead')
            ->where('id', $request->course_lead_id)->first();

            if (!$lead_details) {
                return $this->sendErrorResponse('Data not found', '');
            }

            $updatedData = [
                'tutor_notes' => $request->tutor_notes,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('user_course_student_lead')->where('id', $request->course_lead_id)->update($updatedData);
            
            return $this->sendSuccessResponse('Tutor notes updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Tutor notes updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    public function userCoursePotentialLead(Request $request, $id): JsonResponse
    {
        try {

            $user_id = $id;

            $authUser_id = Auth::user()->id;
            if ($user_id != $authUser_id) {
                return $this->sendErrorResponse('Unauthorized request!', '', 401);
            }

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $categoryIds = DB::table('user_course_student_lead')
                ->where('user_id', '=', $user_id)
                ->pluck('category_id')->toArray();

            $potentialLeads = DB::table('user_course_student_lead as ucsl')
                ->select(
                    'ucsl.id',
                    'ucsl.student_name',
                    'ucsl.student_email',
                    'ucsl.student_phone',
                    'ucsl.created_at',
                    'courses.teaching_mode',
                    'country.name as country',
                    'cities.name as city_name',
                    DB::raw('GROUP_CONCAT(skills.name) as skills'),
                    'psul.unlock_status'
                )
                ->leftJoin('users', 'ucsl.user_id', '=', 'users.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('country', 'users.country', '=', 'country.id')
                ->leftJoin('courses', 'ucsl.course_id', '=', 'courses.id')
                ->leftJoin('courses_skills', 'courses.id', '=', 'courses_skills.course_id')
                ->leftJoin('skills', 'skills.id', '=', 'courses_skills.skill_name')
                //->leftJoin('potential_student_unlock_log as psul', 'psul.user_course_student_lead_id', '=', 'ucsl.id')
                ->leftJoin('potential_student_unlock_log as psul', function ($join) use ($user_id) {
                    $join->on('psul.user_course_student_lead_id', '=', 'ucsl.id')
                        ->where('psul.user_id', '=', $user_id);
                })
                ->whereIn('ucsl.category_id', $categoryIds)
                ->where('ucsl.user_id', '!=', $user_id)
                ->where('psul.user_id', '=', $user_id)
                ->groupBy(
                    'ucsl.id',
                    'ucsl.student_name',
                    'ucsl.student_email',
                    'ucsl.student_phone',
                    'ucsl.created_at',
                    'courses.teaching_mode',
                    'country.name',
                    'cities.name',
                    'psul.unlock_status'
                )
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            $totalCoins = DB::table('user_coin_purchase_history')
                ->where('user_id', '=', $user_id)
                ->SUM('coins_received');

            $totalCoinsConsumed = DB::table('user_coin_consumption_history')
                ->where('user_id', '=', $user_id)
                ->SUM('coins_consumed');

            $remainingCoins = $totalCoins - $totalCoinsConsumed;

            $data = (object)[
                "potentialLeads" => $potentialLeads,
                "totalCoins" => $totalCoins,
                "totalCoinsConsumed" => $totalCoinsConsumed,
                "remainingCoins" => $remainingCoins
            ];

            return $this->sendSuccessResponse('User potential lead fetch successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('User potential lead fetch error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    // public function userCoursePotentialLead(Request $request, $id): JsonResponse
    // {
    //     try {

    //         $user_id = $id;

    //         $authUser_id = Auth::user()->id;
    //         if ($user_id != $authUser_id) {
    //             return $this->sendErrorResponse('Unauthorized request!', '', 401);
    //         }

    //         $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
    //         $perPage = 15;
    //         $categoryIds = DB::table('user_course_student_lead')
    //             ->where('user_id', '=', $user_id)
    //             ->pluck('category_id');

    //         $potentialLeads = DB::table('user_course_student_lead as ucsl')
    //             ->select(
    //                 'ucsl.id',
    //                 'ucsl.student_name',
    //                 'ucsl.student_email',
    //                 'ucsl.student_phone',
    //                 'ucsl.created_at',
    //                 'courses.teaching_mode',
    //                 'country.name as country',
    //                 'cities.name as city_name',
    //                 DB::raw('GROUP_CONCAT(skills.name) as skills'),
    //                 'psul.unlock_status'
    //             )
    //             ->leftJoin('users', 'ucsl.user_id', '=', 'users.id')
    //             ->leftJoin('cities', 'users.city', '=', 'cities.id')
    //             ->leftJoin('country', 'users.country', '=', 'country.id')
    //             ->leftJoin('courses', 'ucsl.course_id', '=', 'courses.id')
    //             ->leftJoin('courses_skills', 'courses.id', '=', 'courses_skills.course_id')
    //             ->leftJoin('skills', 'skills.id', '=', 'courses_skills.skill_name')
    //             ->leftJoin('potential_student_unlock_log as psul', 'psul.user_course_student_lead_id', '=', 'ucsl.id')
    //             ->whereIn('ucsl.category_id', $categoryIds)
    //             ->where('ucsl.user_id', '!=', $user_id)
    //             ->where('psul.user_id', '=', $user_id)
    //             ->groupBy(
    //                 'ucsl.id',
    //                 'ucsl.student_name',
    //                 'ucsl.student_email',
    //                 'ucsl.student_phone',
    //                 'ucsl.created_at',
    //                 'courses.teaching_mode',
    //                 'country.name',
    //                 'city_name',
    //                 'psul.unlock_status'
    //             )
    //             ->paginate($perPage, ['*'], 'page', $pageNumber);

    //         $totalCoins = DB::table('user_coin_purchase_history')
    //             ->where('user_id', '=', $user_id)
    //             ->SUM('coins_received');

    //         $totalCoinsConsumed = DB::table('user_coin_consumption_history')
    //             ->where('user_id', '=', $user_id)
    //             ->SUM('coins_consumed');

    //         $remainingCoins = $totalCoins - $totalCoinsConsumed;

    //         $data = (object)[
    //             "potentialLeads" => $potentialLeads,
    //             "totalCoins" => $totalCoins,
    //             "totalCoinsConsumed" => $totalCoinsConsumed,
    //             "remainingCoins" => $remainingCoins
    //         ];

    //         return $this->sendSuccessResponse('User potential lead fetch successfully.', $data);
    //     } catch (\Throwable $th) {
    //         Log::error('User potential lead fetch error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }

    public function unlockPotentialLead(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'user_course_student_lead_id' => 'required',
                'used_coins' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }
            $student_lead_details = DB::table('user_course_student_lead')->where('id', $request->user_course_student_lead_id)->first();

            if (!$student_lead_details) {
                return $this->sendErrorResponse('Students lead data not found.', '', 404);
            }

            $consumeData = [
                'user_id' => $request->user_id,
                'user_course_student_lead_id' => $request->user_course_student_lead_id,
                'student_name' => $student_lead_details->student_name,
                'coins_consumed' => $request->used_coins,
                'enquiry_date' => $student_lead_details->created_at,
                'coin_consumed_date' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            DB::table('user_coin_consumption_history')->insert($consumeData);

            $insertedData = [
                'user_id' => $request->user_id,
                'user_course_student_lead_id' => $request->user_course_student_lead_id,
                'used_coins' => $request->used_coins,
                'unlock_status' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('potential_student_unlock_log')->insert($insertedData);

            return $this->sendSuccessResponse('User potential lead unlock successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('potential lead unlock error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

}
