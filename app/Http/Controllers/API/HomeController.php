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
use Throwable;
use DB;
use Session;

class HomeController extends BaseController
{

    public function listing(Request $request): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = 15;
            // Build the query
            $query = DB::table('courses')
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
                    'courses.course_logo_preview',
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                    // DB::raw('AVG(student_course_reviews.rating) as average_rating'),
                    // DB::raw('CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(3,1)) as average_rating'
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
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
                    'courses.course_logo_preview',
                    'courses.featured'
                )
                ->limit(4)
                ->orderBy('average_rating', 'desc');

            $query->where('courses.status', 'Approved');
            // Execute the query
            $courseDetails = $query->get();

            // // Cast `average_rating` to a float
            // $courseDetails->transform(function ($item) {
            //     // $item->average_rating = (float)$item->average_rating;
            //     // $item->average_rating = ROUND($item->average_rating, 1);
                
            //     // $castedValue = (float)$item->average_rating;
            //     // $item->average_rating = round($castedValue,1);
                
            //     // $item->average_rating = json_decode($item->average_rating);
                
            //     // $castedValue = (float) $item->average_rating;
            //     // $item->average_rating = number_format(round($castedValue, 1), 1);
                
            //     $castedValue = (float) $item->average_rating;
            //     $item->average_rating = (float) sprintf('%.1f', round($castedValue, 1));

            //     return $item;
            // });

            $categories = DB::table('categories')
                ->orderBy('created_at', 'desc')
                ->get();

            // Build the query
            $webinars = DB::table('webinars')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'webinars.id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.agenda',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.logo',
                    'webinars.preview_logo'
                )
                ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('webinars.start_date', '>=', \Carbon\Carbon::today())
                ->where('webinars.status', 'Approved')
                ->groupBy(
                    'users.f_name',
                    'city_name',
                    'category_name',
                    'webinars.id',
                    'webinars.user_id',
                    'webinars.title',
                    'webinars.agenda',
                    'webinars.start_date',
                    'webinars.end_date',
                    'webinars.start_time',
                    'webinars.fee',
                    'webinars.no_of_seats',
                    'webinars.delivery_mode',
                    'webinars.logo',
                    'webinars.preview_logo',
                    'webinars.agenda',
                )
                ->orderBy('webinars.start_date', 'desc')
                ->limit(3)
                ->get();

            $tutors = DB::table('users')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'cities.name as city_name',
                    DB::raw('GROUP_CONCAT(DISTINCT skills.name) as skill_name'),
                    DB::raw('GROUP_CONCAT(DISTINCT languages.name) as languages'),
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                    DB::raw('COUNT(DISTINCT user_course_student_lead.id) as contactListCount')
                )
                ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
                ->leftJoin('skills', 'skills.id', '=', 'user_skills.skill')
                ->leftJoin('user_languages', 'users.id', '=', 'user_languages.user_id')
                ->leftJoin('languages', 'languages.id', '=', 'user_languages.language')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('student_course_reviews', 'student_course_reviews.user_id', '=', 'users.id')
                ->leftJoin('user_course_student_lead', 'user_course_student_lead.user_id', '=', 'users.id')
                ->where('users.status', 'Approved')
                ->where('users.user_type', '=', 'tutor')
                ->groupBy(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'city_name',
                )->limit(4)->get();
                
            // Transform the skill_name column into an array
            $tutors->transform(function ($tutor) {
                $tutor->skill_name = $tutor->skill_name ? explode(',', $tutor->skill_name) : [];
                return $tutor;
            });
            
            $tutors->transform(function ($tutors) {
                $tutors->languages = $tutors->languages ? explode(',', $tutors->languages) : [];
                return $tutors;
            });
            $subquery = DB::table('student_course_reviews')
            ->select(
                'user_id',
                DB::raw('COALESCE(COUNT(DISTINCT id), 0) as totalReviews'),
                DB::raw('COALESCE(ROUND(AVG(rating), 1), 0) as average_rating')
            )
            ->groupBy('user_id');
            
            $institutions =DB::table('users')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'cities.name as city_name',
                    'subquery.average_rating',
                    'subquery.totalReviews'
                )
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoinSub($subquery, 'subquery', 'subquery.user_id', '=', 'users.id')
                ->where('users.status', 'Approved')
                ->where('users.user_type', '=', 'institute')
                ->get();

            $staticValues = DB::table('page_home')->first();

            $data = (object)[
                'staticValues' => $staticValues,
                'categories' => $categories,
                'courseDetails' => $courseDetails,
                'webinars' => $webinars,
                'tutors' => $tutors,
                'institutions' => $institutions
            ];
            // Return the response
            return $this->sendSuccessResponse('Home page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Home page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    // public function listing(Request $request): JsonResponse
    // {
    //     try {
    //         $pageNumber = request()->input('page', 1);
    //         $perPage = 15;
    //         // Build the query
    //         $query = DB::table('courses')
    //             ->select(
    //                 'users.f_name',
    //                 'cities.name as city_name',
    //                 'categories.name as category_name',
    //                 'skills.name as skill_name',
    //                 'courses.id',
    //                 'courses.user_id',
    //                 'courses.course_name',
    //                 'courses.year_of_exp',
    //                 'courses.duration_value',
    //                 'courses.duration_unit',
    //                 'courses.teaching_mode',
    //                 'courses.batch_type',
    //                 'courses.featured',
    //                 'courses.course_logo',
    //                 'courses.course_logo_preview',
    //                 DB::raw('ROUND(AVG(student_course_reviews.rating), 1) as average_rating'),
    //             )
    //             ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
    //             ->leftJoin('users', 'courses.user_id', '=', 'users.id')
    //             ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
    //             ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
    //             ->leftJoin('cities', 'users.city', '=', 'cities.id')
    //             ->groupBy(
    //                 'users.f_name',
    //                 'city_name',
    //                 'category_name',
    //                 'skill_name',
    //                 'courses.id',
    //                 'courses.user_id',
    //                 'courses.course_name',
    //                 'courses.year_of_exp',
    //                 'courses.duration_value',
    //                 'courses.duration_unit',
    //                 'courses.teaching_mode',
    //                 'courses.batch_type',
    //                 'courses.course_logo',
    //                 'courses.course_logo_preview',
    //                 'courses.featured'
    //             )
    //             ->limit(4)
    //             ->orderBy('average_rating', 'desc');

    //         $query->where('courses.status', '!=', 'Rejected');
    //         // Execute the query
    //         $courseDetails = $query->get();

    //         $categories = DB::table('categories')
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         // Build the query
    //         $webinars = DB::table('webinars')
    //             ->select(
    //                 'users.f_name',
    //                 'cities.name as city_name',
    //                 'categories.name as category_name',
    //                 'webinars.id',
    //                 'webinars.user_id',
    //                 'webinars.title',
    //                 'webinars.agenda',
    //                 'webinars.start_date',
    //                 'webinars.end_date',
    //                 'webinars.start_time',
    //                 'webinars.fee',
    //                 'webinars.no_of_seats',
    //                 'webinars.delivery_mode',
    //                 'webinars.logo',
    //                 'webinars.preview_logo'
    //             )
    //             ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
    //             ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
    //             ->leftJoin('cities', 'users.city', '=', 'cities.id')
    //             ->where('webinars.start_date', '>=', \Carbon\Carbon::today())
    //             ->where('webinars.status', '!=', 'Rejected')
    //             ->groupBy(
    //                 'users.f_name',
    //                 'city_name',
    //                 'category_name',
    //                 'webinars.id',
    //                 'webinars.user_id',
    //                 'webinars.title',
    //                 'webinars.agenda',
    //                 'webinars.start_date',
    //                 'webinars.end_date',
    //                 'webinars.start_time',
    //                 'webinars.fee',
    //                 'webinars.no_of_seats',
    //                 'webinars.delivery_mode',
    //                 'webinars.logo',
    //                 'webinars.preview_logo',
    //                 'webinars.agenda',
    //             )
    //             ->orderBy('webinars.start_date', 'desc')
    //             ->limit(3)
    //             ->get();

    //         $tutors = DB::table('users')
    //             ->select(
    //                 'users.id',
    //                 'f_name',
    //                 'l_name',
    //                 'profile_pic',
    //                 'year_of_exp',
    //                 'address',
    //                 'cities.name as city_name',
    //                 DB::raw('GROUP_CONCAT(user_skills.skill) as skill_name'),
    //             )
    //             ->leftJoin('cities', 'users.city', '=', 'cities.id')
    //             ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
    //             ->where('users.status', '!=', 'Rejected')
    //             ->where('user_type', 'tutor')
    //             ->groupBy(
    //             'users.id',
    //             'f_name',
    //             'l_name',
    //             'profile_pic',
    //             'year_of_exp',
    //             'address',
    //             'cities.name',
    //             )->limit(4)->get();


    //         $institutions = DB::table('users')
    //             ->select(
    //                 'users.id',
    //                 'f_name',
    //                 'l_name',
    //                 'profile_pic',
    //                 'year_of_exp',
    //                 'address',
    //                 'cities.name as city_name',
    //                 'bio'
    //             )
    //             ->leftJoin('cities', 'users.city', '=', 'cities.id')
    //             ->where('users.status', '!=', 'Rejected')
    //             ->where('user_type', 'institute')->limit(4)->get();

    //         $staticValues = DB::table('page_home')->first();

    //         $data = (object)[
    //             'staticValues' => $staticValues,
    //             'categories' => $categories,
    //             'courseDetails' => $courseDetails,
    //             'webinars' => $webinars,
    //             'tutors' => $tutors,
    //             'institutions' => $institutions
    //         ];
    //         // Return the response
    //         return $this->sendSuccessResponse('Home page details fetched successfully.', $data);
    //     } catch (\Throwable $th) {
    //         Log::error('Home page details fetched error: ' . $th->getMessage());
    //         return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
    //     }
    // }

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
                    'users.year_of_exp',

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

                    'courses.first_class_free',
                    'courses.demo_video_url',
                    'courses.course_content',
                    'courses.fee',
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating')
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->groupBy(
                    'users.f_name',

                    'users.profile_pic',
                    'users.bio',
                    'users.year_of_exp',

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

                    'courses.first_class_free',
                    'courses.demo_video_url',
                    'courses.course_content',
                    'courses.featured',
                    'courses.fee',

                );

            // Conditionally apply filters if parameters exist

            $query->where('courses.id', '=', $course_id);

            // Execute the query
            $courseDetails = $query->first();

            $reviews = DB::table('student_course_reviews')
                ->select('student_name', 'student_email', 'student_phone', 'rating', 'review')
                ->where('course_id', $course_id)
                ->get();

            $courseDetails->reviews = $reviews;

            // Return the response
            return $this->sendSuccessResponse('Course details fetched successfully.', $courseDetails);
        } catch (\Throwable $th) {
            Log::error('Course details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function defaultValues(Request $request): JsonResponse
    {
        try {

            $investorConnect = DB::table('settings')->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Default values fetching successfully.', $investorConnect);
        } catch (\Throwable $th) {
            Log::error('Default values error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
