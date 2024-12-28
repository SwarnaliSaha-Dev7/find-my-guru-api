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


class AdminController extends BaseController
{

    //======= Dashboard Section Start ==================
    public function dashboardDetails(Request $request): JsonResponse
    {
        try {

            $start_date = request()->query('start_date');
            $end_date = request()->query('end_date');

            // Build the queries with optional filters
            $usersQuery = DB::table('users')->where('user_type', '!=', 'admin');
            if ($start_date) {
                $usersQuery->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $usersQuery->whereDate('created_at', '<=', $end_date);
            }
            $no_of_users = $usersQuery->count();

            $coursesQuery = DB::table('courses');
            if ($start_date) {
                $coursesQuery->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $coursesQuery->whereDate('created_at', '<=', $end_date);
            }
            $no_of_course = $coursesQuery->count();

            $leadsQuery = DB::table('user_course_student_lead');
            if ($start_date) {
                $leadsQuery->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $leadsQuery->whereDate('created_at', '<=', $end_date);
            }
            $no_of_leads = $leadsQuery->count();

            $subscriptionQuery = DB::table('user_subscription_purchase_history');
            if ($start_date) {
                $subscriptionQuery->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $subscriptionQuery->whereDate('created_at', '<=', $end_date);
            }
            $total_subscription_revenue = $subscriptionQuery->sum('amount_paid');

            $coinQuery = DB::table('user_coin_purchase_history');
            if ($start_date) {
                $coinQuery->whereDate('created_at', '>=', $start_date);
            }
            if ($end_date) {
                $coinQuery->whereDate('created_at', '<=', $end_date);
            }
            $total_coin_revenue = $coinQuery->sum('amount_paid');

            //===========

            $total_revenue = $total_subscription_revenue + $total_coin_revenue;


            $monthlyUser =DB::table('users')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%M') as month"), DB::raw('COUNT(*) as user_count'))
            ->where('user_type', '!=', 'admin')
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%M')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%M')"))
            ->get();


            $monthlyLead = DB::table('user_subscription_purchase_history')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%M') as month"), DB::raw('COUNT(*) as user_count'))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%M')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%M')"))
            ->get();

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            // $subscription_purchase_history = DB::table('user_subscription_purchase_history as usph')
            // ->select('usph.*', 'users.f_name', 'users.l_name')
            // ->leftJoin('users', 'usph.user_id', '=', 'users.id')
            // ->orderBy('usph.created_at', 'desc')
            // ->paginate($perPage, ['*'], 'page', $pageNumber);

            $paymentHistoryQuery = DB::table('payment_history')
                ->select('users.f_name', 'users.l_name', 'payment_history.*', 'cpp.title as coin_package_name', 'sp.title as subscription_name')
                ->leftJoin('users', 'payment_history.user_id', '=', 'users.id')
                ->leftJoin('coin_packages_plans as cpp', 'payment_history.coin_package_id', '=', 'cpp.id')
                ->leftJoin('subscription_plans as sp', 'payment_history.subscription_id', '=', 'sp.id');

                if ($start_date) {
                    $paymentHistoryQuery->whereDate('payment_history.created_at', '>=', $start_date);
                }
                if ($end_date) {
                    $paymentHistoryQuery->whereDate('payment_history.created_at', '<=', $end_date);
                }

                $revenue_history = $paymentHistoryQuery
                ->orderBy('payment_history.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);


            // if (!$subscription_purchase_history) {
            //     return $this->sendErrorResponse('Data not found.', '',);
            // }

            $data = (object)[
                'no_of_users' => $no_of_users,
                'no_of_course' => $no_of_course,
                'no_of_leads' => $no_of_leads,
                'total_revenue' => $total_revenue,
                'monthlyUser' => $monthlyUser,
                'monthlyLead' => $monthlyLead,  
                'revenue_history' => $revenue_history,
            ];


            return $this->sendSuccessResponse('Dashboard details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Dashboard details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //======= Dashboard Section End ==================


    //====== Review Management start ================
    public function userReviewListing(Request $request): JsonResponse
    {
        try {

            $reviews = DB::table('student_course_reviews as scr')
                ->select('courses.course_name', 'users.f_name', 'scr.id', 'scr.student_name', 'scr.student_email', 'scr.student_phone', 'scr.rating', 'scr.review', 'scr.approval_status', 'scr.created_at')
                ->leftJoin('users', 'scr.user_id', '=', 'users.id')
                ->leftJoin('courses', 'scr.course_id', '=', 'courses.id')
                ->orderBy('scr.created_at', 'desc')
                ->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('User reviews fetched successfully.', $reviews);
        } catch (\Throwable $th) {
            Log::error('User reviews fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function fetchReviewDetails(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('student_course_reviews as scr')
            ->select('courses.course_name', 'users.f_name', 'scr.id', 'scr.student_name', 'scr.student_email', 'scr.student_phone', 'scr.rating', 'scr.review', 'scr.approval_status', 'scr.created_at')
            ->leftJoin('users', 'scr.user_id', '=', 'users.id')
            ->leftJoin('courses', 'scr.course_id', '=', 'courses.id')
                ->orderBy('scr.created_at', 'desc')
                ->where('scr.id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Review details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Review details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    public function updateReviewStatus(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'review_id' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $updatedData = [
                'approval_status' => $request->status,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('student_course_reviews')->where('id', $request->review_id)->update($updatedData);
            return $this->sendSuccessResponse('User reviews status updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('User reviews status update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteReview(Request $request, $review_id): JsonResponse
    {
        try {

            $review_id = $review_id; // Optional filter by rating

            if (!$review_id) {
                return $this->sendErrorResponse('Validation Error.', "Review id is required", 403);
            }

            $data = DB::table('student_course_reviews')
                ->select('id')
                ->where('id', $review_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            DB::table('student_course_reviews')->delete($review_id);

            return $this->sendSuccessResponse('User review deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User review deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Review Management end ================
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            //code...
        } catch (\Throwable $th) {
            Log::error('User review deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }


}
