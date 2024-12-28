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
use Illuminate\Support\Facades\File;
use Throwable;
use DB;
use Session;

class DashboardController extends BaseController
{

    public function dashboard(Request $request, $id): JsonResponse
    {
        try {
            $user_id = Auth::user()->id;
            if ($user_id != $id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            if ($startDate) {
                $startDate = date('Y-m-d 23:59:59', strtotime($startDate));
            }
            if ($endDate) {
                $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            }
            //return $this->sendSuccessResponse('Dashboard data fetching successfully.', $startDate );

            if (!$user_id && $user_id !=  $id) {
                return $this->sendErrorResponse('Authentication error!', '', 403);
            }


            $totalLeads = DB::table('user_course_student_lead')
                ->where('user_id', $user_id)
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count();
            $noOfCourses = DB::table('courses')
                ->where('user_id', $user_id)
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count();
            $noOfWebinars = DB::table('webinars')
                ->where('user_id', $user_id)
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count();
            $data = (object)[
                "tota_lLeads" => $totalLeads,
                "no_Of_Courses" => $noOfCourses,
                "no_Of_Webinars" => $noOfWebinars,
            ];
            //$data->noOfWebinars = $noOfWebinars;
            $totalCandidate = DB::table('user_course_student_lead')->count('id');

            $monthlyLeads = DB::table('user_course_student_lead')
                ->select(
                    DB::raw('MONTH(created_at) as month'), // Extract the month
                    DB::raw('YEAR(created_at) as year'),   // Extract the year
                    DB::raw('COUNT(id) as totalLeads')      // Count leads
                )
                ->where('id', $user_id)
                ->groupBy('year', 'month')                 // Group by year and month
                ->orderBy('year', 'asc')                   // Order by year ascending
                ->orderBy('month', 'asc')                  // Order by month ascending
                ->get();

            $monthlyLeadsData = [];
            foreach ($monthlyLeads as $lead) {
                $monthlyLeadsData[] = [
                    'year' => $lead->year,
                    'month' => $lead->month,
                    'totalLeads' => $lead->totalLeads,
                ];
            }

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $coin_purchase_history = DB::table('user_coin_purchase_history as ucph')
                ->select('ucph.id', 'cpp.title', 'ucph.amount_paid', 'ucph.coins_received','ucph.purchase_date', 'ucph.payment_status')
                ->leftJoin('coin_packages_plans as cpp', 'cpp.id', '=', 'ucph.coin_package_id')
                ->where('user_id', $user_id)
                ->orderBy('ucph.created_at', 'desc')->limit(5)->get();

            
            $coin_consume_history =
            DB::table('user_coin_consumption_history as ucch')
            ->select(
                'users.f_name',
                'ucch.id',
                'ucch.enquiry_date',
                'ucch.coin_consumed_date',
                'ucch.coins_consumed',
                'cities.name as location',
                'ucsl.student_name',
                'ucsl.student_email',
                'ucsl.student_phone',
                'ucsl.student_message',
                DB::raw('GROUP_CONCAT(courses_skills.skill_name) as skill_name')
            )
            ->leftJoin('users', 'ucch.user_id', '=', 'users.id')
            ->leftJoin('user_course_student_lead as ucsl', 'ucch.user_course_student_lead_id', '=', 'ucsl.id')
            ->leftJoin('courses_skills', 'ucsl.course_id', '=', 'courses_skills.course_id')
            ->leftJoin('cities', 'users.city', '=', 'cities.id')
            ->groupBy(
                'users.f_name',
                'ucch.id',
                'ucch.enquiry_date',
                'ucch.coin_consumed_date',
                'ucch.coins_consumed',
                'location',
                'ucsl.student_name',
                'ucsl.student_email',
                'ucsl.student_phone',
                'ucsl.student_message',
            )
            ->orderBy('ucch.created_at', 'desc')
            ->where('ucch.user_id', $user_id)->limit(5)->get();

            $data->totalCandidate = $totalCandidate;
            $data->monthlyLeadsData = $monthlyLeadsData;
            $data->coin_purchase_history = $coin_purchase_history;
            $data->coin_consume_history = $coin_consume_history;

            return $this->sendSuccessResponse('Dashboard data fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Dashboard data fetching error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
