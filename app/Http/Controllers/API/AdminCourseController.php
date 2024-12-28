<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Throwable;
use DB;
use Session;


class AdminCourseController extends BaseController
{
    //====== Course Registration start ================
    //===== Courses Listing
    public function courseListing(Request $request): JsonResponse
    {
        try {

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $query = DB::table('courses')
                ->select(
                    'courses.id',
                    'users.f_name',
                    'users.l_name',
                    'users.user_type',
                    'categories.name as category_name',
                    'skills.name as skill_name',
                    'courses.course_name',
                    'courses.created_at',
                    'courses.updated_at',
                    'courses.status'
                )
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
                ->orderBy('courses.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            return $this->sendSuccessResponse('Courses fetched successfully.', $query);
        } catch (\Throwable $th) {
            Log::error('Courses fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //===== Courses Listing by user id
    public function userCourseListing(Request $request, $id): JsonResponse
    {
        try {

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $query = DB::table('courses')
            ->select(
                'courses.id',
                'users.f_name',
                'users.l_name',
                'users.user_type',
                'categories.name as category_name',
                'skills.name as skill_name',
                'courses.course_name',
                'courses.created_at',
                'courses.updated_at',
                'courses.status'
            )
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
            ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
            ->orderBy('courses.created_at', 'desc')
            ->where('courses.user_id', $id)
            ->paginate($perPage, ['*'], 'page', $pageNumber);

            return $this->sendSuccessResponse('Courses fetched successfully.', $query);
        } catch (\Throwable $th) {
            Log::error('Courses fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //===== View Course add page
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

    //===== Insert course details
     public function add(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|integer',
                    'category_id' => 'required|integer',
                    'skill_id' => 'required',
                    'course_name' => 'required',
                    'year_of_exp' => 'required',
                    'duration_value' => 'required',
                    'duration_unit' => 'required',
                    'batch_type' => 'required',
                    'teaching_mode' => 'required',
                    'currency_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            // Save new file
            $path = public_path('uploads');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $fileName = null;
            $filePath = null;
            if ($request->hasFile('course_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo')->getClientOriginalName();
                $request->course_logo->move($path, $fileName);
                $filePath = "uploads/" . $fileName;
            }

            $insertedData = [
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'skill_id' => $request->skill_id,
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
                'course_logo' => $filePath,
                'course_logo_preview' => $request->course_logo_preview,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),

                'mete_title' => $request->mete_title ?? "",
                'meta_description' => $request->meta_description ?? "",
                'meta_keyword' => $request->meta_keyword ?? "",
                'seo1' => $request->seo1 ?? "",
                'seo2' => $request->seo2 ?? "",
                'seo3' => $request->seo3 ?? "",
                'search_tag' => $request->search_tag ?? "",
                'top_tranding_course' => $request->top_tranding_course ?? "",
                'status' => $request->status,

            ];
            //$storeInfo = DB::table('courses')->insert($insertedData);
            $course_id = DB::table('courses')->insertGetId($insertedData);

            if ($request->skill_id && count(json_decode($request->skill_id)) > 0) {
                $skillArr = json_decode($request->skill_id);
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
            // $user_details = DB::table('users')->where('id', $request->user_id)->first();
            // if ($user_details) {
            //     $email = $user_details->email;
            //     $name = $user_details->f_name;

            //     $data = array("email" => $email, "name" => $name, 'course_name' => $request->course_name);
            //     // Send email
            //     Mail::send('email.courseListing', $data, function ($message) use ($email) {
            //         $message->to($email) // Use the recipient's email
            //             ->subject('Your Course Listing is Under Review on FindMyGuru');
            //         $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            //     });

            //     $date = \Carbon\Carbon::now();
            //     $adminData = array("name" => $name, "course_title" => $request->course_name, "course_description" => $request->course_content, "course_fee" => $request->fee, "duration" => $request->duration_value, 'duration_unit' => $request->duration_unit, "teaching_mode" => $request->teaching_mode, "date_added" => $date);
            //     $adminEmail = env('ADMIN_MAIL');
            //     // Send email
            //     Mail::send('email.admin.courseAdditionAlert', $adminData, function ($message) use ($adminEmail) {
            //         $message->to($adminEmail) // Use the recipient's email
            //             ->subject('New Course Added by Tutor on FindMyGuru');
            //         $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            //     });
            // }

            return $this->sendSuccessResponse('Course added successfully.', $course_id);
        } catch (\Throwable $th) {
            Log::error('Course added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }


    //===== View course update page
     public function view_course_update(Request $request, $id): JsonResponse
    {
        try {
            $data = DB::table('courses')
                ->select('users.f_name', 'users.l_name', 'categories.name as category_name', 'courses.*')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
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

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Course details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Course details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    // public function view_course_update(Request $request, $id): JsonResponse
    // {
    //     try {
    //         $data = DB::table('courses')
    //             ->select('users.f_name', 'users.l_name', 'courses.*')
    //             ->leftJoin('users', 'courses.user_id', '=', 'users.id')
    //             ->where('courses.id', $id)
    //             ->first();

    //         if (!$data) {
    //             return $this->sendErrorResponse('Data not found.', '', 404);
    //         }

    //         $categories = DB::table('categories')
    //             ->select('id', 'name', 'is_top_category')
    //             ->get();
    //         $skills = DB::table('skills')
    //             ->select('id', 'skill', 'name')
    //             ->get();
    //         $currency = DB::table('currency')
    //             ->select('id', 'code', 'symbol')
    //             ->get();

    //         $data->categories = $categories;
    //         $data->skills = $skills;
    //         $data->currency = $currency;

    //         if (!$data) {
    //             return $this->sendErrorResponse('Data not found.', '', 404);
    //         }

    //         return $this->sendSuccessResponse('Course details fetched successfully.', $data);
    //     } catch (\Throwable $th) {
    //         Log::error('Course details fetched error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }

    //===== Delete file
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

    //===== course Update
        public function courseUpdate(Request $request, $id): JsonResponse
    {
        try {
            //dd($request->all());

            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'skill_id' => 'required',
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
                    'users.email',
                    'courses.category_id',
                    'courses.course_name',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.batch_type',
                    'courses.teaching_mode',
                    'courses.fee',
                    'courses.course_content',
                    'courses.course_logo',
                )
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->where('courses.id', $id)
                ->first();
            if (!$courseData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }
            // Save new file
            $path = public_path('uploads');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('course_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo')->getClientOriginalName();
                $request->course_logo->move($path, $fileName);
                $filePath = "uploads/" . $fileName;

                if ($courseData->course_logo) {
                    $this->delete_file($courseData->course_logo);
                }
            } else {
                $filePath = $courseData->course_logo;
            }

            $updatedData = [
                'category_id' => $request->category_id,
                'skill_id' => $request->skill_id,
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
                'course_logo' => $filePath,
                'course_logo_preview' => $request->course_logo_preview,
                'updated_at' => \Carbon\Carbon::now(),

                'mete_title' => $request->mete_title,
                'meta_description' => $request->meta_description,
                'meta_keyword' => $request->meta_keyword,
                'seo1' => $request->seo1,
                'seo2' => $request->seo2,
                'seo3' => $request->seo3,
                'search_tag' => $request->search_tag,
                'top_tranding_course' => $request->top_tranding_course,
                'featured' => $request->featured ?? 0,
                'status' => $request->status,
            ];

            $storeInfo = DB::table('courses')->where('id', $id)->update($updatedData);


            if ($request->skill_id && count(json_decode($request->skill_id)) > 0) {
                $skillArr = json_decode($request->skill_id);
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

            if ($request->status && $request->status == 'Approved') {
                $name = $courseData->f_name;
                $email = $courseData->email;
                $data = array("email" => $email,
                    "name" => $name,
                    'course_name' => $request->course_name,
                );

                Mail::send('email.courseListingApproval', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Your Course Listing on FindMyGuru is Now Live!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            if (isset($request->featured) && $request->featured == 1) {
                $name = $courseData->f_name;
                $email = $courseData->email;
                $data = array(
                    "email" => $email,
                    "name" => $name,
                    'course_name' => $request->course_name,
                );

                Mail::send('email.featuredListingConfirmation', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Confirmation: Your Course Featured Listing on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            // $adminData = array(
            //     "user_name" => $courseData->f_name,
            //     "course_name" => $request->course_name,
            //     "course_content" => $request->course_content,
            //     "duration_value" => $request->duration_value,
            //     "duration_unit" => $request->duration_unit,
            //     "teaching_mode" => $request->teaching_mode,
            //     "fee" => $request->fee,
            //     "date" => $date,
            //     "p_course_content" => $courseData->course_content,
            //     "p_duration_value" => $courseData->duration_value,
            //     "p_duration_unit" => $courseData->duration_unit,
            //     "p_teaching_mode" => $courseData->teaching_mode,
            //     "p_fee" => $courseData->fee,
            // );

            // $adminEmail = env('ADMIN_MAIL');
            // // Send email
            // Mail::send('email.admin.courseModificationAlert', $adminData, function ($message) use ($adminEmail) {
            //     $message->to($adminEmail) // Use the recipient's email
            //         ->subject('Course Modification Alert on FindMyGuru');
            //     $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            // });

            return $this->sendSuccessResponse('Course updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Token generation error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    // public function courseUpdate(Request $request, $id): JsonResponse
    // {
    //     try {
    //         //dd($request->all());

    //         $validator = Validator::make($request->all(), [
    //             'category_id' => 'required|integer',
    //             'skill_id' => 'required|integer',
    //             'course_name' => 'required',
    //             'year_of_exp' => 'required',
    //             'duration_value' => 'required',
    //             'duration_unit' => 'required',
    //             'batch_type' => 'required',
    //             'teaching_mode' => 'required',
    //             'currency_id' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
    //         }

    //         $courseData = DB::table('courses')
    //             ->select(
    //                 'users.f_name',
    //                 'users.email',
    //                 'courses.category_id',
    //                 'courses.course_name',
    //                 'courses.duration_value',
    //                 'courses.duration_unit',
    //                 'courses.batch_type',
    //                 'courses.teaching_mode',
    //                 'courses.fee',
    //                 'courses.course_content',
    //                 'courses.course_logo'
    //             )
    //             ->leftJoin('users', 'courses.user_id', '=', 'users.id')
    //             ->where('courses.id', $id)
    //             ->first();
    //         if (!$courseData) {
    //             return $this->sendErrorResponse('Data not found.', '', 404);
    //         }
    //         // Save new file
    //         $path = public_path('uploads');
    //         if (!file_exists($path)) {
    //             mkdir($path, 0777, true);
    //         }

    //         $fileName = null;
    //         $filePath = null;
    //         if ($request->hasFile('course_logo')) {
    //             $fileName = time() . rand(1000, 9999) . "_" . $request->file('course_logo')->getClientOriginalName();
    //             $request->course_logo->move($path, $fileName);
    //             $filePath = "uploads/" . $fileName;

    //             if ($courseData->course_logo) {
    //                 $this->delete_file($courseData->course_logo);
    //             }
    //         } else {
    //             $filePath = $courseData->course_logo;
    //         }

    //         $updatedData = [
    //             'category_id' => $request->category_id,
    //             'skill_id' => $request->skill_id,
    //             'course_name' => $request->course_name,
    //             'year_of_exp' => $request->year_of_exp,
    //             'duration_value' => $request->duration_value,
    //             'duration_unit' => $request->duration_unit,
    //             'batch_type' => $request->batch_type,
    //             'teaching_mode' => $request->teaching_mode,
    //             'fee' => $request->fee,
    //             'currency_id' => $request->currency_id,
    //             'demo_video_url' => $request->demo_video_url,
    //             'course_content' => $request->course_content,
    //             'course_logo' => $filePath,
    //             'updated_at' => \Carbon\Carbon::now(),

    //             'mete_title' => $request->mete_title,
    //             'meta_description' => $request->meta_description,
    //             'meta_keyword' => $request->meta_keyword,
    //             'seo1' => $request->seo1,
    //             'seo2' => $request->seo2,
    //             'seo3' => $request->seo3,
    //             'search_tag' => $request->search_tag,
    //             'top_tranding_course' => $request->top_tranding_course,
    //             'featured' => $request->featured ?? 0,
    //             'status' => $request->status,
    //         ];

    //         $storeInfo = DB::table('courses')->where('id', $id)->update($updatedData);

    //         $date = \Carbon\Carbon::now();

    //         if ($request->status && $request->status == 'Approved') {
    //             $name = $courseData->f_name;
    //             $email = $courseData->email;
    //             $data = array("email" => $email,
    //                 "name" => $name,
    //                 'course_name' => $request->course_name,
    //             );

    //             Mail::send('email.courseListingApproval', $data, function ($message) use ($email) {
    //                 $message->to($email) // Use the recipient's email
    //                     ->subject('Your Course Listing on FindMyGuru is Now Live!');
    //                 $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
    //             });
    //         }

    //         if (isset($request->featured) && $request->featured == 1) {
    //             $name = $courseData->f_name;
    //             $email = $courseData->email;
    //             $data = array(
    //                 "email" => $email,
    //                 "name" => $name,
    //                 'course_name' => $request->course_name,
    //             );

    //             Mail::send('email.featuredListingConfirmation', $data, function ($message) use ($email) {
    //                 $message->to($email) // Use the recipient's email
    //                     ->subject('Confirmation: Your Course Featured Listing on FindMyGuru');
    //                 $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
    //             });
    //         }

    //         // $adminData = array(
    //         //     "user_name" => $courseData->f_name,
    //         //     "course_name" => $request->course_name,
    //         //     "course_content" => $request->course_content,
    //         //     "duration_value" => $request->duration_value,
    //         //     "duration_unit" => $request->duration_unit,
    //         //     "teaching_mode" => $request->teaching_mode,
    //         //     "fee" => $request->fee,
    //         //     "date" => $date,
    //         //     "p_course_content" => $courseData->course_content,
    //         //     "p_duration_value" => $courseData->duration_value,
    //         //     "p_duration_unit" => $courseData->duration_unit,
    //         //     "p_teaching_mode" => $courseData->teaching_mode,
    //         //     "p_fee" => $courseData->fee,
    //         // );

    //         // $adminEmail = env('ADMIN_MAIL');
    //         // // Send email
    //         // Mail::send('email.admin.courseModificationAlert', $adminData, function ($message) use ($adminEmail) {
    //         //     $message->to($adminEmail) // Use the recipient's email
    //         //         ->subject('Course Modification Alert on FindMyGuru');
    //         //     $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
    //         // });

    //         return $this->sendSuccessResponse('Course updated successfully.', $storeInfo);
    //     } catch (\Throwable $th) {
    //         Log::error('Token generation error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }

    public function deleteCourse(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('courses')
                ->select('id', 'course_logo')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            if ($data->course_logo) {
                $this->delete_file($data->course_logo);
            }

            DB::table('courses')->delete($id);
            DB::table('user_course_student_lead')->where('course_id', $id)->delete();
            DB::table('student_course_reviews')->where('course_id', $id)->delete();

            return $this->sendSuccessResponse('Course`s details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Course`s details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Course Registration end ================

    //====== Business Registration start ================

    //====== Users listing
    public function userListing(Request $request): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;

            $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.user_type',
                    'users.f_name',
                    'users.l_name',
                    'users.email',
                    'users.phone',
                    'users.profile_pic',
                    'users.status',
                    'users.created_at',
                    DB::raw('(SELECT end_date FROM user_subscription_purchase_history as usph WHERE usph.user_id = users.id ORDER BY created_at DESC LIMIT 1) as last_purchase_end_date'),

                    DB::raw('((SELECT COALESCE(SUM(coins_received), 0)
                    FROM user_coin_purchase_history as ucph
                    WHERE ucph.user_id = users.id) -
                    (SELECT COALESCE(SUM(coins_consumed), 0)
                    FROM user_coin_consumption_history as ucch
                    WHERE ucch.user_id = users.id)) as remaining_coins')
                )
                ->where('users.user_type', '!=', 'admin')
                ->orderBy('users.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);


            if (!$query) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }
            return $this->sendSuccessResponse('Users details fetched successfully.', $query);
        } catch (\Throwable $th) {
            Log::error('Users details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Update user details
    public function userDetailsInsert(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'f_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|digits:10',
                'user_type' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }



            $path = public_path('uploads/profile_pic');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('profile_pic')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('profile_pic')->getClientOriginalName();
                $request->profile_pic->move($path, $fileName);
                $filePath = "uploads/profile_pic/" . $fileName;

            }
            //$insertedData['profile_pic'] = $filePath;
            $insertedData = [
                'user_type' => $request->user_type,
                'f_name' => $request->f_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'bio' => $request->bio,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'profile_pic' => $filePath,
                'preview_profile_pic' => $request->preview_profile_pic,
                'year_of_exp' => $request->year_of_exp,
                'postcode' => $request->postcode,
                'gst_no' => $request->gst_no,
                'status' => $request->status,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $id = DB::table('users')->insertGetId($insertedData);

            if ($request->qualification && count(json_decode($request->qualification)) > 0
            ) {
                $qualificationArr = json_decode($request->qualification);
                DB::table('user_qualifications')->where('user_id', $id)->delete();

                foreach ($qualificationArr as $x) {
                    $insertedData = [
                            'user_id' => $id,
                            'qualification' => $x,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ];
                    DB::table('user_qualifications')->insert($insertedData);
                }
            }

            if ($request->skill && count(json_decode($request->skill)) > 0) {
                $skillArr = json_decode($request->skill);
                DB::table('user_skills')->where('user_id', $id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'user_id' => $id,
                        'skill' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_skills')->insert($insertedData);
                }
            }

            if ($request->language && count(json_decode($request->language)) > 0) {
                $languageArr = json_decode($request->language);
                DB::table('user_languages')->where('user_id', $id)->delete();

                foreach ($languageArr as $x) {
                    $insertedData = [
                        'user_id' => $id,
                        'language' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_languages')->insert($insertedData);
                }
            }

            return $this->sendSuccessResponse('User details inserted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User`s details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Fetch user details
    public function userDetails(Request $request, $id): JsonResponse
    {
        try {
            $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.user_type',
                    'users.f_name',
                    'users.l_name',
                    'users.email',
                    'users.phone',
                    'users.profile_pic',

                    'users.bio',
                    'users.country',
                    'users.state',
                    'users.city',
                    'users.area',
                    'users.address',
                    'users.year_of_exp',
                    'users.postcode',
                    'users.gst_no',
                    'users.status',

                    'users.created_at',
                )
                ->where('users.id', $id)

                ->first();

            if (!$query) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $skill = DB::table('user_skills')
                // ->leftJoin('skills','skills.id','user_skills.skill')
                // ->select('skills.id as value','skills.name as label')
                ->where('user_id', $id)
                // ->get();
                ->pluck('skill')
                // ->map(fn($value) => (int) $value);
                ->implode(',');

            $qualification = DB::table('user_qualifications')
                // ->leftJoin('qualifications','qualifications.id','user_qualifications.qualification')
                // ->select('qualifications.id as value','qualifications.name as label')
                ->where('user_id', $id)
                // ->get();
                ->pluck('qualification')
                ->implode(',');

            $language = DB::table('user_languages')
                // ->leftJoin('languages','languages.id','user_languages.language')
                // ->select('languages.id as value','languages.name as label')
                ->where('user_id', $id)
                // ->get();
                ->pluck('language')
                ->implode(',');

            $query->skill = $skill;
            $query->qualification = $qualification;
            $query->language = $language;

            $skills = DB::table('skills')
                ->get();
            $states = DB::table('states')
                ->get();
            $country = DB::table('country')
                ->get();
            $areas = DB::table('areas')
                ->get();
            $qualifications = DB::table('qualifications')
                ->get();

            $query->skills = $skills;
            $query->states = $states;
            $query->countries = $country;
            $query->areas = $areas;
            $query->qualifications = $qualifications;

            return $this->sendSuccessResponse('User`s details fetched successfully.', $query);
        } catch (\Throwable $th) {
            Log::error('User`s details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Update user details
    public function userDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'f_name' => 'required',
                'email' => 'required|email',
                'phone' => 'required|digits:10',
                'user_type' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('users')
                ->select('id', 'email', 'profile_pic')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }
            if ($request->email !== $data->email) {
                $emailExists = DB::table('users')->where('email', $request->email)
                    ->where('id', '!=', $id) // Exclude the current user's ID
                    ->exists();

                if ($emailExists) {
                    return $this->sendErrorResponse('Email already in use.', '', 400);
                }
                $updatedData['email'] = $request->email;
            }
            $updatedData = [
                'user_type' => $request->user_type,
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'phone' => $request->phone,
                'bio' => $request->bio,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'area' => $request->area,
                'address' => $request->address,
                'gst_no' => $request->gst_no,
                'status' => $request->status,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            //update password
            if($request->password){
                $updatedData['password'] = bcrypt($request->password);
            }

            $path = public_path('uploads/profile_pic');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            // $filePath = $data->profile_pic;
            $filePath = null;
            if ($request->hasFile('profile_pic')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('profile_pic')->getClientOriginalName();
                $request->profile_pic->move($path, $fileName);
                $filePath = "uploads/profile_pic/" . $fileName;

                if ($data->profile_pic) {
                    $this->delete_file($data->profile_pic);
                }

                //update preview profile pic
                $updatedData['preview_profile_pic'] = $request->preview_profile_pic;
            }
            else{
                $filePath = $request->profile_pic;
            }
            $updatedData['profile_pic'] = $filePath;


            if ($request->qualification && count(json_decode($request->qualification)) > 0) {
                $qualificationArr = json_decode($request->qualification);
                DB::table('user_qualifications')->where('user_id', $id)->delete();

                foreach ($qualificationArr as $x) {
                    $insertedData = [
                        'user_id' => $id,
                        'qualification' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_qualifications')->insert($insertedData);
                }
            }

            if ($request->skill && count(json_decode($request->skill)) > 0) {
                $skillArr = json_decode($request->skill);
                DB::table('user_skills')->where('user_id', $id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'user_id' => $id,
                        'skill' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_skills')->insert($insertedData);
                }
            }

            if ($request->language && count(json_decode($request->language)) > 0) {
                $languageArr = json_decode($request->language);
                DB::table('user_languages')->where('user_id', $id)->delete();

                foreach ($languageArr as $x) {
                    $insertedData = [
                        'user_id' => $id,
                        'language' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_languages')->insert($insertedData);
                }
            }
            $storeInfo = DB::table('users')->where('id', $id)->update($updatedData);
            //===== Send email
            if ($request->status && $request->status == 'Approved' ) {
                $name = $request->f_name;
                $email = $request->email;
                $data = array("email" => $email, "name" => $name);

                Mail::send('email.profileApproval', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Your FindMyGuru Profile Has Been Approved!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('User details updated successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User`s details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Delete user record
    public function deleteUser(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('users')
                ->select('id', 'email', 'profile_pic')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            if ($data->profile_pic) {
                $this->delete_file($data->profile_pic);
            }

            DB::table('users')->delete($id);
            DB::table('user_coin_purchase_history')->where('user_id', $id)->delete();
            DB::table('user_coin_consumption_history')->where('user_id', $id)->delete();
            DB::table('user_subscription_purchase_history')->where('user_id', $id)->delete();

            DB::table('courses')->where('user_id', $id)->delete();
            DB::table('user_course_student_lead')->where('user_id', $id)->delete();
            DB::table('student_course_reviews')->where('user_id', $id)->delete();
            DB::table('potential_student_unlock_log')->where('user_id', $id)->delete();

            DB::table('webinars')->where('user_id', $id)->delete();
            DB::table('user_webinar_student_lead')->where('user_id', $id)->delete();

            DB::table('user_skills')->where('user_id', $id)->delete();
            DB::table('user_languages')->where('user_id', $id)->delete();
            DB::table('user_qualifications')->where('user_id', $id)->delete();

            return $this->sendSuccessResponse('User`s details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User`s details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Coin purchage history by user id
  public function coinPurchaseHistory(Request $request, $id): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;

            $data = DB::table('user_coin_purchase_history as ucph')
                ->select('ucph.id', 'users.f_name', 'cpp.title', 'ucph.amount_paid', 'ucph.coins_received', 'ucph.purchase_date')
                ->leftJoin('users', 'ucph.user_id', '=', 'users.id')
                ->leftJoin('coin_packages_plans as cpp', 'cpp.id', '=', 'ucph.coin_package_id')
                ->where('ucph.user_id', $id)
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '',);
            }

            return $this->sendSuccessResponse('Coins purchase history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('About us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }


    //====== Coin purchase details
    public function coinPurchaseDetails(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('user_coin_purchase_history as ucph')
                ->select('ucph.id', 'users.f_name', 'cpp.title', 'ucph.amount_paid', 'ucph.coins_received', 'ucph.purchase_date')
                ->leftJoin('users', 'ucph.user_id', '=', 'users.id')
                ->leftJoin('coin_packages_plans as cpp', 'cpp.id', '=', 'ucph.coin_package_id')
                ->orderBy('ucph.created_at', 'desc')
                ->where('ucph.id', $id)->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '',);
            }

            return $this->sendSuccessResponse('Coins purchase history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('About us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    //====== Delete coin purchase record
    public function deleteCoinPurchaseRecord(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('user_coin_purchase_history')
                ->where('id', $id)
                ->exists();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            DB::table('user_coin_purchase_history')->delete($id);

            return $this->sendSuccessResponse('Coin purchase details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Coin purchase details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //======= Coins Consumed

    //======= Coin usage history by user id
    public function coinConsumedHistory(Request$request, $id): JsonResponse
    {
        try {

            // $user_id = Auth::user()->id;
            // if ($user_id != $id
            // ) {
            //     return $this->sendErrorResponse('Authentication error.', '');
            // }

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;

            $data = DB::table('user_coin_consumption_history as ucch')
                ->select('courses.course_name', 'ucsl.student_name', 'ucsl.student_email', 'ucsl.student_phone', 'ucsl.student_message', 'ucch.coins_consumed', 'ucch.coin_consumed_date')
                ->leftJoin('users', 'ucch.user_id', '=', 'users.id')
                ->leftJoin('user_course_student_lead as ucsl', 'ucch.user_course_student_lead_id', '=', 'ucsl.id')
                ->leftJoin('courses', 'ucsl.course_id', '=', 'courses.id')
                ->orderBy('ucch.created_at', 'desc')
                ->where('ucch.user_id', $id)
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '',);
            }

            return $this->sendSuccessResponse('Coin consumed history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('About us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    //====== Subscription Purchase History by user id
      public function subscriptionPurchaseHistory(Request$request, $id): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $purchaseHistory = DB::table('user_subscription_purchase_history as usph')
                ->select('usph.id','sp.title as package_name', 'usph.subcription_date as purchase_date', 'usph.end_date', 'usph.amount_paid')
                ->leftJoin('users', 'usph.user_id', '=', 'users.id')
                ->leftJoin('subscription_plans as sp', 'usph.subcription_id', '=', 'sp.id')
                ->orderBy('usph.created_at', 'desc')
                ->where('usph.user_id', $id)
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            $userDetails = DB::table('users')
            ->select('users.f_name', 'users.user_type', 'sp.title as current_package_name', 'usph.subcription_date as purchase_date', 'usph.end_date')
            ->leftJoin('user_subscription_purchase_history as usph', 'usph.user_id', '=', 'users.id')
            ->leftJoin('subscription_plans as sp', 'usph.subcription_id', '=', 'sp.id')
            ->orderBy('usph.created_at', 'desc')
            ->where('users.id', $id)->first();

            $data = (object)[
                'userDetails' => $userDetails,
                'purchaseHistory' => $purchaseHistory,
            ];

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Subscription history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Subscription history fetching error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    // public function subscriptionPurchaseHistory(Request$request, $id): JsonResponse
    // {
    //     try {
    //         $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
    //         $perPage = 15;
    //         $data = DB::table('user_subscription_purchase_history as usph')
    //             ->select('usph.*', 'users.f_name', 'users.l_name')
    //             ->leftJoin('users', 'usph.user_id', '=', 'users.id')
    //             ->orderBy('usph.created_at', 'desc')
    //             ->where('usph.user_id', $id)
    //             ->paginate($perPage, ['*'], 'page', $pageNumber);

    //         //$studentLead = $query->first();

    //         return $this->sendSuccessResponse('Subscription history fetching successfully.', $data);
    //     } catch (\Throwable $th) {
    //         Log::error('Subscription history fetching error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }
    //===== Purchase details
    public function subscriptionPurchaseDetails(Request $request, $id): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $data = DB::table('user_subscription_purchase_history as usph')
                ->select('usph.*', 'users.f_name', 'users.l_name')
                ->leftJoin('users', 'usph.user_id', '=', 'users.id')
                ->where('usph.id', $id)->first();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Subscription history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Subscription history fetching error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Delete subscription purchase record
    public function deleteSubscriptionPurchaseRecord(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('user_subscription_purchase_history')
                ->where('id', $id)
                ->exists();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            DB::table('user_subscription_purchase_history')->delete($id);

            return $this->sendSuccessResponse('Coin purchase details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Coin purchase details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Business Registration end ================
}
