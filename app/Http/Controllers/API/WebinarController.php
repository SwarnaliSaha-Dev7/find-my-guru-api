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
use PhpParser\Node\Stmt\TryCatch;
use Session;

class WebinarController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */


    public function webinar_add_page(Request $request): JsonResponse
    {
        try {
            $categories = DB::table('categories')
                ->get();
            $currency = DB::table('currency')
                ->get();

            $data = (object)[
                'categories' => $categories,
                'currency' => $currency
            ];

            // if (!$data) {
            //     return $this->sendErrorResponse( 'Not found.', '');
            // }

            return $this->sendSuccessResponse('Webinar page details fetch successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Webinar page details fetch error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    
    public function add(Request $request): JsonResponse
    {
        try {
            
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'category_id' => 'required|integer',
                'title' => 'required',
                'languages' => 'required',
                'start_date' => 'required',
                'start_time' => 'required',
                'fee' => 'required',
                'no_of_seats' => 'required',
                'agenda' => 'required',
                'content' => 'required',
                'logo' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $user_id = Auth::user()->id;
            if ($user_id != $request->user_id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }

            // Save new file
            $path = public_path('uploads/webinar/');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('logo')->getClientOriginalName();
                $request->logo->move($path, $fileName);
                $filePath = "uploads/webinar/" . $fileName;
            }

            $insertedData = [
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'language' => $request->languages,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'fee' => $request->fee,
                'currency_id' => $request->currency_id,
                'no_of_seats' => $request->no_of_seats,
                'delivery_mode' => $request->delivery_mode,
                'agenda' => $request->agenda,
                'content' => $request->content,
                'logo' => $filePath,
                'preview_logo' => $request->preview_logo,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            if ($request->delivery_mode == 'Offline' || $request->delivery_mode == 'Both') {
                $insertedData = [
                    'address' => $request->address
                ];
            }
            $webinar_id = DB::table('webinars')->insertGetId($insertedData);

            if ($request->languages && count(json_decode($request->languages)) > 0) {
                $skillArr = json_decode($request->languages);
                //DB::table('courses_skills')->where('course_id', $request->course_id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'webinar_id' => $webinar_id,
                        'language_id' => $x,
                    ];
                    DB::table('webinar_language')->insert($insertedData);
                }
            }

            return $this->sendSuccessResponse('Webinar added successfully.', $webinar_id);
        } catch (\Throwable $th) {
            Log::error('Webinar added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    // public function add(Request $request): JsonResponse
    // {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|integer',
    //             'category_id' => 'required|integer',
    //             'title' => 'required',
    //             'language' => 'required',
    //             'start_date' => 'required',
    //             'start_time' => 'required',
    //             'fee' => 'required',
    //             'no_of_seats' => 'required',
    //             'agenda' => 'required',
    //             'currency_id' => 'required',
    //             'logo' => 'required',

    //         ]);

    //         if ($validator->fails()) {
    //             return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
    //         }

    //         // Save new file
    //         $path = public_path('uploads/webinar/');
    //         if (!file_exists($path)) {
    //             mkdir($path, 0777, true);
    //         }
    //         $fileName = null;
    //         $filePath = null;
    //         if ($request->hasFile('logo')) {
    //             $fileName = time() . rand(1000, 9999) . "_" . $request->file('logo')->getClientOriginalName();
    //             $request->logo->move($path, $fileName);
    //             $filePath = "uploads/webinar/" . $fileName;
    //         }

    //         // $preview_fileName = null;
    //         // $preview_filePath = null;
    //         // if ($request->hasFile('preview_logo')) {
    //         //     $preview_fileName = time() . rand(1000, 9999) . "_" . $request->file('preview_logo')->getClientOriginalName();
    //         //     $request->preview_logo->move($path, $preview_fileName);
    //         //     $preview_filePath = "uploads/webinar/" . $preview_fileName;
    //         // }

    //         $insertedData = [
    //             'user_id' => $request->user_id,
    //             'category_id' => $request->category_id,
    //             'title' => $request->title,
    //             'language' => $request->language,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'start_time' => $request->start_time,
    //             'end_time' => $request->end_time,
    //             'fee' => $request->fee,
    //             'currency_id' => $request->currency_id,
    //             'no_of_seats' => $request->no_of_seats,
    //             'delivery_mode' => $request->delivery_mode,
    //             'address' => $request->address,
    //             'agenda' => $request->agenda,
    //             'logo' => $filePath,
    //             'preview_logo' => $request->preview_logo,
    //             'created_at' => \Carbon\Carbon::now(),
    //             'updated_at' => \Carbon\Carbon::now(),
    //         ];
    //         $webinar_id = DB::table('webinars')->insertGetId($insertedData);

    //         if ($request->language && count(json_decode($request->language)) > 0) {
    //             $skillArr = json_decode($request->language);
    //             //DB::table('courses_skills')->where('course_id', $request->course_id)->delete();

    //             foreach ($skillArr as $x) {
    //                 $insertedData = [
    //                     'webinar_id' => $webinar_id,
    //                     'language_id' => $x,
    //                 ];
    //                 DB::table('webinar_language')->insert($insertedData);
    //             }
    //         }

    //         return $this->sendSuccessResponse('Webinar added successfully.', $webinar_id);
    //     } catch (\Throwable $th) {
    //         Log::error('Webinar added error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }

    public function view_webinar_update(Request $request, $id): JsonResponse
    {
        try {
            $data = DB::table('webinars')
                ->select(
                    'category_id',
                    'categories.name as category_name',
                    'title',
                    'start_date',
                    'end_date',
                    'start_time',
                    'end_time',
                    'fee',
                    'currency_id',
                    'no_of_seats',
                    'delivery_mode',
                    'address',
                    'agenda',
                    'content',
                    'logo',
                    'preview_logo'
                )
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->where('webinars.id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Not found.', '');
            }


            $language = DB::table('webinar_language')
            ->where('webinar_id', $id)
            ->pluck('language_id')
            ->toArray();

            $languages = DB::table('languages')->select('id as value', 'name as label')->whereIn('id', $language)->get();

             $data->languages = $languages;
            // $data->currency = $currency;

            return $this->sendSuccessResponse('Webinar details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Webinar details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function delete_file($path)
    {
        //$file_path = public_path('path/to/your/file.txt');
        $file_path = public_path($path);
        if (File::exists($file_path)) {
            File::delete($file_path);
            //echo 'File deleted successfully.';
        } else {
            //echo 'File does not exist.';
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        try {
            //dd($webinar_id);

            $validator = Validator::make($request->all(), [
                //'webinar_id' => 'required|integer',
                'category_id' => 'required|integer',
                'title' => 'required',
                'languages' => 'required',
                'start_date' => 'required',
                'start_time' => 'required',
                'fee' => 'required',
                'no_of_seats' => 'required',
                'agenda' => 'required',
                'content' => 'required',
                'currency_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('webinars')
                ->select(
                    'id',
                    'user_id',
                    'category_id',
                    'title',
                    'language',
                    'start_date',
                    'end_date',
                    'start_time',
                    'end_time',
                    'fee',
                    'currency_id',
                    'no_of_seats',
                    'delivery_mode',
                    'address',
                    'agenda',
                    'content',
                    'logo',
                    'preview_logo'
                )
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            $user_id = Auth::user()->id;
            if ($user_id != $data->user_id) {
                return $this->sendErrorResponse('Authentication error!', '');
            }

            // Save new file
            $path = public_path('uploads/webinar/');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('logo')->getClientOriginalName();
                $request->logo->move($path, $fileName);
                $filePath = "uploads/webinar/" . $fileName;

                if ($data->logo) {
                    $this->delete_file($data->logo);
                }
            } else {
                $filePath = $data->logo;
            }

            // $preview_fileName = null;
            // $preview_filePath = null;
            // if ($request->hasFile('preview_logo')) {
            //     $preview_fileName = time() . rand(1000, 9999) . "_" . $request->file('preview_logo')->getClientOriginalName();
            //     $request->preview_logo->move($path, $preview_fileName);
            //     $preview_filePath = "uploads/webinar/" . $preview_fileName;

            //     if ($data->preview_logo) {
            //         $this->delete_file($data->preview_logo);
            //     }
            // } else {
            //     $preview_filePath = $data->preview_logo;
            // }

            $updatedData = [
                'category_id' => $request->category_id,
                'title' => $request->title,
                'language' => $request->languages,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'fee' => $request->fee,
                'currency_id' => $request->currency_id,
                'no_of_seats' => $request->no_of_seats,
                'delivery_mode' => $request->delivery_mode,
                'agenda' => $request->agenda,
                'content' => $request->content,
                'logo' => $filePath,
                'preview_logo' => $request->preview_logo,
                //'status' =>  'Pending',
                'updated_at' => \Carbon\Carbon::now(),
            ];

            if ($request->delivery_mode == 'Offline' || $request->delivery_mode == 'Both') {
                $updatedData = [
                    'address' => $request->address,
                ];
            }

            $storeInfo = DB::table('webinars')->where('id', $data->id)->update($updatedData);

            if ($request->languages && count(json_decode($request->languages)) > 0) {
                $skillArr = json_decode($request->languages);
                DB::table('webinar_language')->where('webinar_id', $data->id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'webinar_id' => $data->id,
                        'language_id' => $x,
                    ];
                    DB::table('webinar_language')->insert($insertedData);
                }
            }

            return $this->sendSuccessResponse('Webinar updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Webinar updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    // public function update(Request $request, $id): JsonResponse
    // {
    //     try {
    //         //dd($webinar_id);

    //         $validator = Validator::make($request->all(), [
    //             //'webinar_id' => 'required|integer',
    //             'category_id' => 'required|integer',
    //             'title' => 'required',
    //             'language' => 'required',
    //             'start_date' => 'required',
    //             'start_time' => 'required',
    //             'fee' => 'required',
    //             'no_of_seats' => 'required',
    //             'address' => 'required',
    //             'agenda' => 'required',
    //             'currency_id' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
    //         }

    //         $data = DB::table('webinars')
    //             ->select(
    //                 'id',
    //                 'user_id',
    //                 'category_id',
    //                 'title',
    //                 'language',
    //                 'start_date',
    //                 'end_date',
    //                 'start_time',
    //                 'end_time',
    //                 'fee',
    //                 'currency_id',
    //                 'no_of_seats',
    //                 'delivery_mode',
    //                 'address',
    //                 'agenda',
    //                 'logo',
    //                 'preview_logo'
    //             )
    //             ->where('id', $id)
    //             ->first();

    //         if (!$data) {
    //             return $this->sendErrorResponse('Data not found.', '');
    //         }

    //         $user_id = Auth::user()->id;
    //         if ($user_id != $data->user_id) {
    //             return $this->sendErrorResponse('Authentication error!', '');
    //         }

    //         // Save new file
    //         $path = public_path('uploads/webinar/');
    //         if (!file_exists($path)) {
    //             mkdir($path, 0777, true);
    //         }

    //         $fileName = null;
    //         $filePath = null;
    //         if ($request->hasFile('logo')) {
    //             $fileName = time() . rand(1000, 9999) . "_" . $request->file('logo')->getClientOriginalName();
    //             $request->logo->move($path, $fileName);
    //             $filePath = "uploads/webinar/" . $fileName;

    //             if ($data->logo) {
    //                 $this->delete_file($data->logo);
    //             }
    //         } else {
    //             $filePath = $data->logo;
    //         }

    //         // $preview_fileName = null;
    //         // $preview_filePath = null;
    //         // if ($request->hasFile('preview_logo')) {
    //         //     $preview_fileName = time() . rand(1000, 9999) . "_" . $request->file('preview_logo')->getClientOriginalName();
    //         //     $request->preview_logo->move($path, $preview_fileName);
    //         //     $preview_filePath = "uploads/webinar/" . $preview_fileName;

    //         //     if ($data->preview_logo) {
    //         //         $this->delete_file($data->preview_logo);
    //         //     }
    //         // } else {
    //         //     $preview_filePath = $data->preview_logo;
    //         // }

    //         $updatedData = [
    //             'category_id' => $request->category_id,
    //             'title' => $request->title,
    //             'language' => $request->language,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'start_time' => $request->start_time,
    //             'end_time' => $request->end_time,
    //             'fee' => $request->fee,
    //             'currency_id' => $request->currency_id,
    //             'no_of_seats' => $request->no_of_seats,
    //             'delivery_mode' => $request->delivery_mode,
    //             'address' => $request->address,
    //             'agenda' => $request->agenda,
    //             'logo' => $filePath,
    //             'preview_logo' => $request->preview_logo,
    //             'updated_at' => \Carbon\Carbon::now(),
    //         ];

    //         $storeInfo = DB::table('webinars')->where('id', $data->id)->update($updatedData);

    //         if ($request->language && count(json_decode($request->language)) > 0) {
    //             $skillArr = json_decode($request->language);
    //             DB::table('webinar_language')->where('webinar_id', $data->id)->delete();

    //             foreach ($skillArr as $x) {
    //                 $insertedData = [
    //                     'webinar_id' => $data->id,
    //                     'language_id' => $x,
    //                 ];
    //                 DB::table('webinar_language')->insert($insertedData);
    //             }
    //         }

    //         return $this->sendSuccessResponse('Webinar updated successfully.', $storeInfo);
    //     } catch (\Throwable $th) {
    //         Log::error('Webinar updated error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }

    public function delete(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('webinars')
                ->select('id','user_id', 'logo')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }
            $user_id = Auth::user()->id;
            if ($user_id != $data->user_id) {
                return $this->sendErrorResponse('Unauthorized request!', '', 401);
            }
            
            if ($data->logo) {
                $this->delete_file($data->logo);
            }

            DB::table('webinars')->delete($id);
            DB::table('user_webinar_student_lead')->where('webinar_id', $id)->delete();

            return $this->sendSuccessResponse('Webinar deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Webinar delete error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    public function listing(Request $request): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            // Get filter inputs from the request
            //$rating = $request->input('fee'); // Optional filter by rating
            $location = $request->input('location'); // Optional filter by city
            $teaching_mode = $request->input('teaching_mode'); // Optional filter by teaching mode
            $fee = $request->input('fee'); // Optional filter by fee
            $skill = $request->input('skill'); // Optional filter by skill
            $sortby = $request->input('sortby'); // Optional sorting by rating

            // Build the query
            $query = DB::table('webinars')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'webinars.id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.logo',
                    'webinars.preview_logo',
                    DB::raw('COUNT(DISTINCT user_webinar_student_lead.id) as contactListCount')
                )
                ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('user_webinar_student_lead', 'user_webinar_student_lead.webinar_id', '=', 'webinars.id')
                ->where('webinars.status', 'Approved')
                ->where('webinars.start_date', '>=', \Carbon\Carbon::today())
                ->groupBy(
                    'users.f_name',
                    'city_name',
                    'category_name',
                    'webinars.id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.logo',
                    'webinars.preview_logo'
                );
                // ->orderBy('webinars.start_date', 'desc');
                
            // if (is_null($location) && is_null($teaching_mode) && is_null($fee) && is_null($sortby)){
            if (is_null($sortby)){
                $query->orderBy('webinars.start_date', 'desc');
            }

            // Conditionally apply filters if parameters exist

            if (!is_null($fee)) {
                $query->having('webinars.fee', '<=', $fee);
            }

            // if (!is_null($skill)) {
            //     $query->where('skills.name', 'like', '%' . $skill . '%');
            // }

            if (!is_null($location)) {
                $query->where(DB::raw('LOWER(cities.name)'), 'like', '%' . strtolower($location) . '%');
            }

            if (!is_null($teaching_mode)) {
                $query->where('webinars.delivery_mode', '=', $teaching_mode);
            }
            
            if (!is_null($skill)) {
                // $query->where('skills.name', 'like', '%' . $skill . '%');
                $searchedSkillCategories = DB::table('skills')
                                                ->where('name', 'like', '%'.$skill.'%')
                                                ->distinct()
                                                ->pluck('category_id');

                $query->whereIn('webinars.category_id', $searchedSkillCategories);
            }

            // Apply sorting if provided
            if ($sortby == 'price-high') {
                $query->orderBy('webinars.fee', 'desc');
            }
            elseif ($sortby == 'price-low') {
                $query->orderBy('webinars.fee', 'asc');
            }
            elseif ($sortby == 'popular') {
                $query->orderBy('contactListCount', 'desc');
            }

            // Execute the query
            $webinarDetails = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            // Return the response
            return $this->sendSuccessResponse('Webinars fetched successfully.', $webinarDetails);
        } catch (\Throwable $th) {
            Log::error('Course listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function userWebinarListing(Request $request, $user_id): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            // Get filter inputs from the request
            $user_id = $user_id;
            $start_date = $request->input('start_date'); // Optional filter by start_date
            $end_date = $request->input('end_date'); // Optional filter by end_date
            $filter = $request->input('filter'); // Optional filter by teaching mode

            // Build the query 
            $query = DB::table('webinars')
                ->select(
                    'categories.name as category_name',
                    'webinars.id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.end_time',
                    'webinars.status'
                )
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->groupBy(
                    'category_name',
                    'webinars.id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.end_time',
                    'webinars.status'
                )
                ->orderBy('webinars.start_date', 'desc');

            // Conditionally apply filters if parameters exist
            if (!is_null($user_id)) {
                $query->where('webinars.user_id', '=', $user_id);
            }
            if (!is_null($start_date) && !is_null($end_date)) {
                $query->whereBetween('webinars.start_date', [$start_date, $end_date]);
            }

            if (!is_null($filter)) {
                $query->where('webinars.status', '=', $filter);
            }

            // Execute the query
            $webinarDetails = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            // Return the response
            return $this->sendSuccessResponse('Webinars fetched successfully.', $webinarDetails);
        } catch (\Throwable $th) {
            Log::error('Webinar fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function UpcomingWebinarListing(Request $request, $user_id): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            // Get filter inputs from the request
            $user_id = $user_id; // Optional filter by rating

            // Build the query'title', 'language', 'start_date', 'end_date', 'start_time','end_time', 'fee', 'currency_id', 'no_of_seats', 'delivery_mode', 'address', 'agenda',
            $query = DB::table('webinars')
                ->select(

                    'categories.name as category_name',
                    'webinars.id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.status',
                    'webinars.agenda'

                )
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->groupBy(
                    'category_name',
                    'webinars.id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.status',
                    'webinars.agenda'
                )
                ->orderBy('webinars.start_date', 'desc');
            $query->where('webinars.status','Approved');
            $query->where('webinars.start_date', '>=', \Carbon\Carbon::today());
            // Conditionally apply filters if parameters exist
            if (!is_null($user_id)) {
                $query->where('webinars.user_id', '=', $user_id);
            }


            // Execute the query
            $webinarDetails = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            // Return the response
            return $this->sendSuccessResponse('Upcoming Webinar fetched successfully.', $webinarDetails);
        } catch (\Throwable $th) {
            Log::error('Upcoming Webinar error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function webinarDetails(Request $request, $id): JsonResponse
    {

        try {
            // Get filter inputs from the request
            $webinar_id = $id; // Optional filter by rating

            // Build the query
            $query = DB::table('webinars')
                ->select(
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.bio',
                    'users.year_of_exp',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'webinars.id',
                    'webinars.category_id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.end_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.agenda',
                    'webinars.content',
                    'webinars.logo',
                    'webinars.preview_logo',
                    'webinars.demo_video_url'
                )
                ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->groupBy(
                    'users.f_name',
                    'users.l_name',

                    'users.profile_pic',
                    'users.bio',
                    'users.year_of_exp',

                    'city_name',
                    'category_name',
                    'webinars.id',
                    'webinars.category_id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.end_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.agenda',
                    'webinars.logo',
                    'webinars.preview_logo',
                    'webinars.demo_video_url'

                );

            // Conditionally apply filters if parameters exist

            $query->where('webinars.id', '=', $webinar_id);

            // Execute the query
            $webinarDetails = $query->first();

            $lang = DB::table('webinar_language')
                ->where('webinar_id', $webinar_id)
                ->pluck('language_id')
                ->toArray();

            $languages = DB::table('languages')->whereIn('id',
                $lang
            )
            ->pluck('name')
            ->toArray();

            $webinarDetails->languages = $languages;

            $related_webinar = DB::table('webinars')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'webinars.id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.logo',
                    'webinars.preview_logo',
                    'webinars.agenda'
                )
                ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('webinars.start_date', '>=', \Carbon\Carbon::today())
                ->where('webinars.status','Approved')
                ->where('webinars.id', '!=', $webinar_id)
                ->where('webinars.category_id', '=', $webinarDetails->category_id)
                ->groupBy(
                    'users.f_name',
                    'city_name',
                    'category_name',
                    'webinars.id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.logo',
                    'webinars.preview_logo',
                    'webinars.agenda'
                )
                ->orderBy('webinars.start_date', 'desc')
                ->get();

            $webinarDetails->related_webinar = $related_webinar;

            // Return the response
            return $this->sendSuccessResponse('Webinar details fetched successfully.', $webinarDetails);
        } catch (\Throwable $th) {
            Log::error('Webinar details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    
    public function contactWithTrainer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'webinar_id' => 'required',
                'student_name' => 'required',
                'student_email' => 'required|email',
                'student_phone' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $webinar_details = DB::table('webinars')
            ->select('webinars.title', 'webinars.user_id', 'users.email', 'users.f_name')
            ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
            ->where('webinars.id', $request->webinar_id)->first();

            if (!$webinar_details) {
                return $this->sendErrorResponse('Data not found', '');
            }

            $insertedData = [
                'user_id' => $webinar_details->user_id,
                'webinar_id' => $request->webinar_id,
                'webinar_title' => $webinar_details->title,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('user_webinar_student_lead')->insert($insertedData);

            $email = $webinar_details->email;
            $data = array(
                "name" => $webinar_details->f_name,
                'webinar_title' => $webinar_details->title,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone
            );
            // Send email
            Mail::send('email.webinarLeadGeneration', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('New Student Lead for Your Webinar on FindMyGuru!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('Trainer will contact you soon.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Token generation error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    

    public function userWebinarLead(Request $request, $id): JsonResponse
    {
        try {

            $start_date = $request->input('start_date'); // Optional filter by start_date
            $end_date = $request->input('end_date'); // Optional filter by end_date
            $user_id = $id;
            $query = DB::table('user_webinar_student_lead')->where('user_id', $user_id);

            if (!is_null($start_date) && !is_null($end_date)) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            $studentLead = $query->get();
            return $this->sendSuccessResponse('User students lead fetch successfully.', $studentLead);
        } catch (\Throwable $th) {
            Log::error('Token generation error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }
}
